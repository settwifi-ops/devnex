<?php
// app/Services/RealTradingExecutionService.php

namespace App\Services;

use App\Models\User;
use App\Models\AiDecision;
use App\Models\PendingOrder;
use App\Services\Cache\TradingCacheService;
use App\Jobs\ProcessTradeBatch;
use App\Jobs\RefreshUserDataJob;
use App\Jobs\SyncPendingOrdersJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RealTradingExecutionService
{
    private $binanceAccountService;
    private $tradingCache;
    
    // Konfigurasi Risk Management
    private $stopLossPercentage = 2.0; // 2% stop loss
    private $takeProfitPercentage = 4.0; // 4% take profit
    private $riskPerTrade = 0.02; // 2% risk per trade
    private $orderExpiryMinutes = 15; // 15 menit expiry
    private $leverage = 5; // 5x leverage
    private $batchSize = 25; // Process 25 users per batch
    
    // Rate limiting
    private $maxTradesPerMinute = 50;
    private $maxUsersPerBatch = 100;

    public function __construct(
        BinanceAccountService $binanceAccountService,
        TradingCacheService $tradingCache
    ) {
        $this->binanceAccountService = $binanceAccountService;
        $this->tradingCache = $tradingCache;
    }

    /**
     * ✅ ENHANCED: Execute REAL trade dengan queue system untuk ratusan user
     */
    public function executeRealTrade(AiDecision $decision): array
    {
        Log::info("🚀 REAL TRADING: Starting execution for {$decision->action} {$decision->symbol}");
        
        // 1. Cek rate limiting untuk symbol ini
        $rateLimit = $this->tradingCache->limitSymbolTrading($decision->symbol);
        if (!$rateLimit['allowed']) {
            Log::warning("Rate limit exceeded for symbol {$decision->symbol}");
            return [
                'success' => false,
                'message' => 'Rate limit exceeded. Please wait before trading this symbol again.',
                'retry_after' => $rateLimit['retry_after'] ?? 60,
                'users_processed' => 0
            ];
        }
        
        // 2. Cek jika trade sudah sedang berjalan untuk symbol ini
        if ($this->tradingCache->isTradeInProgress($decision->symbol)) {
            $progress = $this->tradingCache->getTradeProgress($decision->symbol);
            Log::info("Trade already in progress for {$decision->symbol}", $progress ?? []);
            
            return [
                'success' => false,
                'message' => 'Trade is already being processed for this symbol.',
                'users_in_progress' => $progress['user_ids'] ?? [],
                'started_at' => $progress['started_at'] ?? null
            ];
        }
        
        // 3. Get eligible users dengan optimasi query
        $eligibleUsers = $this->getEligibleUsers();
        
        if ($eligibleUsers->isEmpty()) {
            Log::warning("No eligible users for real trading");
            return [
                'success' => false,
                'message' => 'No eligible users found for trading.',
                'users_processed' => 0
            ];
        }
        
        Log::info("🎯 Found {$eligibleUsers->count()} eligible users for {$decision->symbol}");
        
        // 4. Mark trade as in progress di cache
        $this->tradingCache->markTradeInProgress(
            $decision->symbol, 
            $eligibleUsers->pluck('id')->toArray()
        );
        
        // 5. Dispatch jobs dalam batch
        $batchesDispatched = $this->dispatchTradeBatches($eligibleUsers, $decision);
        
        // 6. Return real-time progress tracking
        return [
            'success' => true,
            'message' => 'Trade execution started for ' . $eligibleUsers->count() . ' users.',
            'total_users' => $eligibleUsers->count(),
            'batches_dispatched' => $batchesDispatched,
            'symbol' => $decision->symbol,
            'action' => $decision->action,
            'progress_key' => "trade:progress:{$decision->symbol}",
            'estimated_completion' => now()->addMinutes(5)->toISOString()
        ];
    }
    
    /**
     * ✅ Get eligible users dengan optimasi query
     */
    private function getEligibleUsers()
    {
        // Gunakan chunking untuk menghindari memory issues
        return User::query()
            ->select(['users.id', 'users.email'])
            ->with(['portfolio' => function($query) {
                $query->select(['id', 'user_id', 'real_balance', 'real_trading_enabled', 'real_trading_active'])
                      ->where('real_trading_enabled', true)
                      ->where('real_trading_active', true)
                      ->where('real_balance', '>=', 11);
            }])
            ->whereHas('portfolio', function($query) {
                $query->where('real_trading_enabled', true)
                      ->where('real_trading_active', true)
                      ->where('real_balance', '>=', 11);
            })
            ->whereHas('binanceAccounts', function($query) {
                $query->select('id')->active()->verified();
            })
            ->limit($this->maxUsersPerBatch) // Limit untuk safety
            ->get()
            ->filter(function($user) {
                // Double check dengan cache
                $cachedBalance = $this->tradingCache->getBalance($user->id);
                $balance = $cachedBalance['total'] ?? $user->portfolio->real_balance ?? 0;
                
                return $balance >= 11 && 
                       !$this->tradingCache->isUserTrading($user->id);
            });
    }
    
    /**
     * ✅ Dispatch trade jobs dalam batch
     */
    private function dispatchTradeBatches($users, AiDecision $decision): int
    {
        $userIds = $users->pluck('id')->toArray();
        $batchesDispatched = 0;
        
        // Chunk users untuk batch processing
        foreach (array_chunk($userIds, $this->batchSize) as $batchIndex => $batchUserIds) {
            try {
                // Dispatch batch job dengan delay bertahap
                ProcessTradeBatch::dispatch($batchUserIds, $decision->id)
                    ->onQueue('trading_batch')
                    ->delay(now()->addSeconds($batchIndex * 2)); // Staggered start
                
                $batchesDispatched++;
                
                Log::info("Dispatched batch {$batchIndex} for {$decision->symbol}", [
                    'users' => count($batchUserIds),
                    'delay' => $batchIndex * 2 . ' seconds'
                ]);
                
            } catch (\Exception $e) {
                Log::error("Failed to dispatch batch {$batchIndex}: " . $e->getMessage());
            }
        }
        
        // Schedule cleanup job untuk nanti
        $this->scheduleCleanupJob($decision->symbol, $userIds);
        
        return $batchesDispatched;
    }
    
    /**
     * ✅ Schedule cleanup job setelah semua batch selesai
     */
    private function scheduleCleanupJob(string $symbol, array $userIds): void
    {
        // Dispatch job untuk cleanup setelah 10 menit
        \App\Jobs\CleanupTradeProgress::dispatch($symbol, $userIds)
            ->delay(now()->addMinutes(10))
            ->onQueue('trading');
    }
    
    /**
     * ✅ METHOD SINGLE USER: Untuk manual execution atau retry
     */
    public function executeForUser(User $user, AiDecision $decision): array
    {
        Log::info("👤 Executing trade for single user {$user->id} - {$decision->symbol}");
        
        try {
            // 1. Validasi user
            $validation = $this->validateUserForTrading($user);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'user_id' => $user->id
                ];
            }
            
            // 2. Cek duplicate orders
            if ($this->hasDuplicateOrder($user->id, $decision->symbol)) {
                return [
                    'success' => false,
                    'message' => 'User already has pending order for this symbol',
                    'user_id' => $user->id,
                    'symbol' => $decision->symbol
                ];
            }
            
            // 3. Mark user as trading
            $this->tradingCache->setUserTrading($user->id, true);
            
            // 4. Execute trade dalam transaction
            $result = DB::transaction(function () use ($user, $decision) {
                return $this->executeTradeWithSLTP($user, $decision);
            });
            
            // 5. Update cache dengan hasil trade
            if ($result['success']) {
                $this->updateUserTradeCache($user->id, $result['order_id'] ?? null);
            }
            
            // 6. Reset trading state
            $this->tradingCache->setUserTrading($user->id, false);
            
            // 7. Trigger background data refresh
            RefreshUserDataJob::dispatch($user->id);
            
            return array_merge($result, ['user_id' => $user->id]);
            
        } catch (\Exception $e) {
            Log::error("Single user trade execution failed for user {$user->id}: " . $e->getMessage());
            
            // Reset trading state
            $this->tradingCache->setUserTrading($user->id, false);
            
            return [
                'success' => false,
                'message' => 'Trade execution failed: ' . $e->getMessage(),
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * ✅ Validate user untuk trading
     */
    private function validateUserForTrading(User $user): array
    {
        // 1. Cek portfolio
        if (!$user->portfolio) {
            return ['valid' => false, 'message' => 'User portfolio not found'];
        }
        
        $portfolio = $user->portfolio;
        
        // 2. Cek trading enabled
        if (!$portfolio->real_trading_enabled || !$portfolio->real_trading_active) {
            return ['valid' => false, 'message' => 'Trading not enabled'];
        }
        
        // 3. Cek balance (gunakan cache dulu)
        $cachedBalance = $this->tradingCache->getBalance($user->id);
        $balance = $cachedBalance['total'] ?? $portfolio->real_balance;
        
        if ($balance < 11) {
            return ['valid' => false, 'message' => 'Insufficient balance (minimum $11 required)'];
        }
        
        // 4. Cek Binance account
        if (!$user->binanceAccounts()->active()->verified()->exists()) {
            return ['valid' => false, 'message' => 'No active Binance account'];
        }
        
        // 5. Cek rate limiting
        $rateLimit = $this->tradingCache->limitUserApiCall($user->id);
        if (!$rateLimit['allowed']) {
            return [
                'valid' => false, 
                'message' => 'Rate limit exceeded. Please wait before trading again.',
                'retry_after' => $rateLimit['retry_after']
            ];
        }
        
        // 6. Cek jika user sedang trading
        if ($this->tradingCache->isUserTrading($user->id)) {
            return ['valid' => false, 'message' => 'User is currently trading'];
        }
        
        return ['valid' => true, 'message' => 'User validated successfully', 'balance' => $balance];
    }
    
    /**
     * ✅ Cek duplicate orders
     */
    private function hasDuplicateOrder(int $userId, string $symbol): bool
    {
        return PendingOrder::where('user_id', $userId)
            ->where('symbol', $symbol)
            ->whereIn('status', ['PENDING', 'PARTIALLY_FILLED', 'NEW'])
            ->where('expires_at', '>', now())
            ->exists();
    }
    
    /**
     * ✅ Execute trade dengan Stop Loss & Take Profit
     */
    private function executeTradeWithSLTP(User $user, AiDecision $decision): array
    {
        try {
            // 1. Get Binance instance
            $binance = $this->binanceAccountService->getBinanceInstance($user->id);
            
            // 2. Get balance (gunakan cache atau fetch)
            $balance = $this->getUserBalance($user->id, $binance);
            
            // 3. Calculate position size
            $positionSize = $this->calculatePositionSize($balance, $decision->price);
            
            // 4. Set leverage
            $this->setLeverage($binance, $decision->symbol, $this->leverage);
            
            // 5. Calculate SL/TP prices
            $positionType = $this->getPositionTypeFromAction($decision->action);
            $stopLossPrice = $this->calculateStopLossPrice($decision->price, $positionType);
            $takeProfitPrice = $this->calculateTakeProfitPrice($decision->price, $positionType);
            
            // 6. Place LIMIT order
            $order = $this->placeLimitOrder(
                $binance, 
                $decision->symbol, 
                $positionType, 
                $positionSize['quantity'], 
                $decision->price
            );
            
            if (!$order['success']) {
                throw new \Exception($order['message']);
            }
            
            $mainOrderId = $order['order_id'];
            
            // 7. Place Stop Loss order
            $stopLossOrderId = $this->placeStopLossOrder(
                $binance,
                $decision->symbol,
                $positionType,
                $positionSize['quantity'],
                $stopLossPrice
            );
            
            // 8. Place Take Profit order
            $takeProfitOrderId = $this->placeTakeProfitOrder(
                $binance,
                $decision->symbol,
                $positionType,
                $positionSize['quantity'],
                $takeProfitPrice
            );
            
            // 9. Save to database
            $pendingOrder = $this->savePendingOrder(
                $user->id,
                $decision,
                $positionType,
                $positionSize,
                $mainOrderId,
                $stopLossOrderId,
                $takeProfitOrderId,
                $stopLossPrice,
                $takeProfitPrice
            );
            
            Log::info("✅ Trade executed successfully for user {$user->id}", [
                'symbol' => $decision->symbol,
                'order_id' => $mainOrderId,
                'amount' => $positionSize['amount'],
                'quantity' => $positionSize['quantity']
            ]);
            
            return [
                'success' => true,
                'message' => 'Trade executed successfully',
                'order_id' => $mainOrderId,
                'pending_order_id' => $pendingOrder->id,
                'symbol' => $decision->symbol,
                'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
                'quantity' => $positionSize['quantity'],
                'amount' => $positionSize['amount'],
                'stop_loss' => $stopLossPrice,
                'take_profit' => $takeProfitPrice,
                'sl_order_id' => $stopLossOrderId,
                'take_profit_order_id' => $takeProfitOrderId
            ];
            
        } catch (\Exception $e) {
            Log::error("Trade execution failed for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * ✅ Get user balance dengan cache optimization
     */
    private function getUserBalance(int $userId, $binance): float
    {
        // Cek cache dulu
        $cachedBalance = $this->tradingCache->getBalance($userId);
        
        if ($cachedBalance && isset($cachedBalance['total'])) {
            // Cek jika data masih fresh (kurang dari 30 detik)
            $cacheAge = time() - ($cachedBalance['timestamp'] ?? 0);
            if ($cacheAge < 30) {
                return $cachedBalance['total'];
            }
        }
        
        // Jika cache tidak ada atau stale, fetch dari Binance
        try {
            $balance = $this->fetchBinanceBalance($binance);
            
            // Update cache
            $this->tradingCache->cacheBalance($userId, $balance);
            
            return $balance['total'];
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch balance for user {$userId}: " . $e->getMessage());
            
            // Return cached value sebagai fallback, atau 0
            return $cachedBalance['total'] ?? 0;
        }
    }
    
    /**
     * ✅ Fetch balance dari Binance
     */
    private function fetchBinanceBalance($binance): array
    {
        $total = 0;
        $available = 0;
        
        try {
            // Coba futures balance
            if (method_exists($binance, 'futuresAccountBalance')) {
                $futuresBalance = $binance->futuresAccountBalance();
                
                foreach ($futuresBalance as $asset) {
                    if (isset($asset['asset']) && $asset['asset'] === 'USDT') {
                        $total = (float) $asset['balance'];
                        $available = (float) ($asset['availableBalance'] ?? 0);
                        break;
                    }
                }
            }
            
            // Jika futures balance 0, coba spot
            if ($total <= 0 && method_exists($binance, 'account')) {
                $accountInfo = $binance->account();
                
                if (isset($accountInfo['balances'])) {
                    foreach ($accountInfo['balances'] as $balance) {
                        if ($balance['asset'] === 'USDT') {
                            $total = (float) $balance['free'] + (float) $balance['locked'];
                            $available = (float) $balance['free'];
                            break;
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch Binance balance: " . $e->getMessage());
        }
        
        return [
            'total' => $total,
            'available' => $available,
            'timestamp' => time(),
            'currency' => 'USDT'
        ];
    }
    
    /**
     * ✅ Calculate position size dengan risk management
     */
    private function calculatePositionSize(float $balance, float $entryPrice): array
    {
        // Risk amount: 2% dari balance
        $riskAmount = $balance * $this->riskPerTrade;
        
        // Batasan: Min $11, Max $50
        $riskAmount = max(11, min($riskAmount, 50));
        
        // Quantity berdasarkan entry price
        $quantity = $riskAmount / $entryPrice;
        
        return [
            'amount' => $riskAmount,
            'quantity' => $quantity,
            'risk_percentage' => $this->riskPerTrade * 100,
            'balance_used' => $riskAmount
        ];
    }
    
    /**
     * ✅ Place LIMIT order
     */
    private function placeLimitOrder($binance, string $symbol, string $positionType, float $quantity, float $price): array
    {
        try {
            $side = $positionType === 'LONG' ? 'BUY' : 'SELL';
            
            $order = $binance->futuresOrder(
                $side,
                $symbol,
                $quantity,
                $price,
                'LIMIT',
                [
                    'timeInForce' => 'GTC',
                    'leverage' => $this->leverage
                ]
            );
            
            if (!isset($order['orderId'])) {
                throw new \Exception("Limit order failed: " . json_encode($order));
            }
            
            return [
                'success' => true,
                'order_id' => $order['orderId'],
                'status' => $order['status'] ?? 'NEW'
            ];
            
        } catch (\Exception $e) {
            Log::error("Limit order placement failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * ✅ Place STOP LOSS order
     */
    private function placeStopLossOrder($binance, string $symbol, string $positionType, float $quantity, float $stopPrice): ?string
    {
        try {
            $side = $positionType === 'LONG' ? 'SELL' : 'BUY';
            
            $order = $binance->futuresOrder(
                $side,
                $symbol,
                $quantity,
                0, // Price 0 untuk STOP_MARKET
                'STOP_MARKET',
                [
                    'stopPrice' => $stopPrice,
                    'closePosition' => 'true',
                    'reduceOnly' => 'true'
                ]
            );
            
            return $order['orderId'] ?? null;
            
        } catch (\Exception $e) {
            Log::warning("Stop loss order failed (non-critical): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ✅ Place TAKE PROFIT order
     */
    private function placeTakeProfitOrder($binance, string $symbol, string $positionType, float $quantity, float $takeProfitPrice): ?string
    {
        try {
            $side = $positionType === 'LONG' ? 'SELL' : 'BUY';
            
            $order = $binance->futuresOrder(
                $side,
                $symbol,
                $quantity,
                $takeProfitPrice,
                'LIMIT',
                [
                    'timeInForce' => 'GTC',
                    'reduceOnly' => 'true'
                ]
            );
            
            return $order['orderId'] ?? null;
            
        } catch (\Exception $e) {
            Log::warning("Take profit order failed (non-critical): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ✅ Save pending order ke database
     */
    private function savePendingOrder(
        int $userId,
        AiDecision $decision,
        string $positionType,
        array $positionSize,
        string $mainOrderId,
        ?string $stopLossOrderId,
        ?string $takeProfitOrderId,
        float $stopLossPrice,
        float $takeProfitPrice
    ) {
        return PendingOrder::create([
            'user_id' => $userId,
            'ai_decision_id' => $decision->id,
            'symbol' => $decision->symbol,
            'binance_order_id' => $mainOrderId,
            'sl_order_id' => $stopLossOrderId,
            'take_profit_order_id' => $takeProfitOrderId,
            'limit_price' => $decision->price,
            'stop_loss_price' => $stopLossPrice,
            'take_profit_price' => $takeProfitPrice,
            'quantity' => $positionSize['quantity'],
            'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
            'position_type' => $positionType,
            'amount' => $positionSize['amount'],
            'expires_at' => now()->addMinutes($this->orderExpiryMinutes),
            'status' => 'PENDING',
            'status' => 'NEW',
            'notes' => "Risk: {$positionSize['risk_percentage']}%, SL: {$this->stopLossPercentage}%, TP: {$this->takeProfitPercentage}%"
        ]);
    }
    
    /**
     * ✅ Update user trade cache setelah trade executed
     */
    private function updateUserTradeCache(int $userId, ?string $orderId): void
    {
        try {
            // Invalidate positions cache (karena ada order baru)
            $this->tradingCache->invalidateUserCache($userId);
            
            // Update last trade timestamp
            $this->tradingCache->setUserState($userId, 'last_trade', [
                'time' => now()->timestamp,
                'order_id' => $orderId
            ], 300); // 5 menit TTL
            
        } catch (\Exception $e) {
            Log::warning("Failed to update user trade cache: " . $e->getMessage());
        }
    }
    
    /**
     * ✅ METHOD: Check pending orders yang expired
     */
    public function checkPendingOrders(): array
    {
        Log::info("🕒 Checking expired pending orders");
        
        $results = [
            'expired' => 0,
            'cancelled' => 0,
            'failed' => 0
        ];
        
        try {
            // Get expired orders dengan chunking
            $expiredOrders = PendingOrder::where('status', 'PENDING')
                ->where('expires_at', '<=', now())
                ->chunk(100, function ($orders) use (&$results) {
                    foreach ($orders as $order) {
                        try {
                            if ($this->cancelExpiredOrderWithSLTP($order)) {
                                $results['cancelled']++;
                            }
                        } catch (\Exception $e) {
                            Log::error("Failed to cancel order {$order->id}: " . $e->getMessage());
                            $results['failed']++;
                        }
                    }
                });
            
            $results['expired'] = $results['cancelled'] + $results['failed'];
            
            Log::info("✅ Checked expired orders", $results);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error("❌ Check pending orders failed: " . $e->getMessage());
            return $results;
        }
    }
    
    /**
     * ✅ Cancel expired order beserta SL/TP orders
     */
    private function cancelExpiredOrderWithSLTP(PendingOrder $order): bool
    {
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($order->user_id);
            $cancelled = [];
            
            // Cancel main order
            if ($order->binance_order_id) {
                try {
                    $binance->futuresCancel($order->symbol, $order->binance_order_id);
                    $cancelled[] = 'main';
                } catch (\Exception $e) {
                    // Order mungkin sudah filled atau cancelled
                }
            }
            
            // Cancel stop loss order
            if ($order->sl_order_id) {
                try {
                    $binance->futuresCancel($order->symbol, $order->sl_order_id);
                    $cancelled[] = 'stop_loss';
                } catch (\Exception $e) {
                    // Order mungkin sudah triggered
                }
            }
            
            // Cancel take profit order
            if ($order->take_profit_order_id) {
                try {
                    $binance->futuresCancel($order->symbol, $order->take_profit_order_id);
                    $cancelled[] = 'take_profit';
                } catch (\Exception $e) {
                    // Order mungkin sudah filled
                }
            }
            
            // Update order status
            $order->update([
                'status' => 'EXPIRED',
                'notes' => 'Automatically expired. Cancelled: ' . implode(', ', $cancelled)
            ]);
            
            // Invalidate user cache
            $this->tradingCache->invalidateUserCache($order->user_id);
            
            return true;
            
        } catch (\Exception $e) {
            // Mark as expired meski cancel gagal
            $order->update([
                'status' => 'EXPIRED',
                'notes' => 'Auto expiry (cancel failed: ' . $e->getMessage() . ')'
            ]);
            
            return false;
        }
    }
    
    /**
     * ✅ METHOD: Add stop loss to filled orders
     */
    public function addStopLossToFilledOrders($userId = null): array
    {
        Log::info("🔧 Adding stop loss to filled orders", ['user_id' => $userId]);
        
        $results = [
            'total_checked' => 0,
            'stop_loss_added' => 0,
            'failed' => 0
        ];
        
        try {
            $query = PendingOrder::where('status', 'FILLED')
                ->whereNull('sl_order_id')
                ->whereNotNull('binance_order_id');
            
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $query->chunk(50, function ($orders) use (&$results) {
                foreach ($orders as $order) {
                    $results['total_checked']++;
                    
                    try {
                        if ($this->addStopLossToFilledOrder($order)) {
                            $results['stop_loss_added']++;
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to add stop loss to order {$order->id}: " . $e->getMessage());
                        $results['failed']++;
                    }
                }
            });
            
            Log::info("✅ Stop loss addition completed", $results);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error("❌ Add stop loss to filled orders failed: " . $e->getMessage());
            return $results;
        }
    }
    
    /**
     * ✅ Add stop loss to single filled order
     */
    private function addStopLossToFilledOrder(PendingOrder $order): bool
    {
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($order->user_id);
            
            // Calculate stop loss price
            $stopLossPrice = $this->calculateStopLossPrice(
                $order->limit_price ?? $order->avg_price ?? $order->executed_price,
                $order->position_type
            );
            
            $stopLossSide = $order->side === 'BUY' ? 'SELL' : 'BUY';
            
            // Place stop loss order
            $stopLossOrder = $binance->futuresOrder(
                $stopLossSide,
                $order->symbol,
                $order->quantity,
                0, // Price 0 untuk STOP_MARKET
                'STOP_MARKET',
                [
                    'stopPrice' => $stopLossPrice,
                    'closePosition' => 'true',
                    'reduceOnly' => 'true'
                ]
            );
            
            if (!isset($stopLossOrder['orderId'])) {
                throw new \Exception("Stop loss order failed: " . json_encode($stopLossOrder));
            }
            
            // Update order
            $order->update([
                'sl_order_id' => $stopLossOrder['orderId'],
                'stop_loss_price' => $stopLossPrice,
                'notes' => $order->notes . " | Stop loss added post-fill"
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * ✅ METHOD: Get trading statistics
     */
    public function getTradingStatistics($userId = null): array
    {
        $stats = [
            'total_users' => 0,
            'active_traders' => 0,
            'total_pending_orders' => 0,
            'total_open_positions' => 0,
            'cache_stats' => [],
            'rate_limits' => []
        ];
        
        try {
            // User statistics
            $stats['total_users'] = User::count();
            $stats['active_traders'] = User::whereHas('portfolio', function($q) {
                $q->where('real_trading_enabled', true)
                  ->where('real_trading_active', true);
            })->count();
            
            // Order statistics
            $stats['total_pending_orders'] = PendingOrder::whereIn('status', ['PENDING', 'PARTIALLY_FILLED'])->count();
            $stats['total_open_positions'] = PendingOrder::where('status', 'FILLED')->count();
            
            // Cache statistics
            $stats['cache_stats'] = $this->tradingCache->getStats();
            
            // Rate limit stats (contoh)
            $stats['rate_limits'] = [
                'max_trades_per_minute' => $this->maxTradesPerMinute,
                'batch_size' => $this->batchSize,
                'max_users_per_batch' => $this->maxUsersPerBatch
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get trading statistics: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * ✅ METHOD: Force refresh user data
     */
    public function forceRefreshUserData($userId): array
    {
        try {
            // Invalidate semua cache user
            $this->tradingCache->invalidateUserCache($userId);
            
            // Dispatch refresh job dengan high priority
            RefreshUserDataJob::dispatch($userId)
                ->onQueue('trading')
                ->delay(now()->addSeconds(1));
            
            // Dispatch sync orders job
            SyncPendingOrdersJob::dispatch($userId)
                ->delay(now()->addSeconds(5));
            
            return [
                'success' => true,
                'message' => 'User data refresh initiated',
                'user_id' => $userId,
                'jobs_dispatched' => 2
            ];
            
        } catch (\Exception $e) {
            Log::error("Force refresh failed for user {$userId}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Refresh failed: ' . $e->getMessage(),
                'user_id' => $userId
            ];
        }
    }
    
    /**
     * ✅ HELPER: Set leverage
     */
    private function setLeverage($binance, $symbol, $leverage)
    {
        try {
            if (method_exists($binance, 'futures_change_leverage')) {
                $binance->futures_change_leverage($symbol, $leverage);
            } elseif (method_exists($binance, 'change_leverage')) {
                $binance->change_leverage($symbol, $leverage);
            }
        } catch (\Exception $e) {
            Log::warning("Leverage setting failed: " . $e->getMessage());
        }
    }
    
    /**
     * ✅ HELPER: Calculate Stop Loss Price
     */
    private function calculateStopLossPrice($entryPrice, $positionType): float
    {
        $percentage = $this->stopLossPercentage / 100;
        
        if ($positionType === 'LONG') {
            return $entryPrice * (1 - $percentage);
        } else {
            return $entryPrice * (1 + $percentage);
        }
    }
    
    /**
     * ✅ HELPER: Calculate Take Profit Price
     */
    private function calculateTakeProfitPrice($entryPrice, $positionType): float
    {
        $percentage = $this->takeProfitPercentage / 100;
        
        if ($positionType === 'LONG') {
            return $entryPrice * (1 + $percentage);
        } else {
            return $entryPrice * (1 - $percentage);
        }
    }
    
    /**
     * ✅ HELPER: Get position type from action
     */
    private function getPositionTypeFromAction($action): string
    {
        return $action === 'BUY' ? 'LONG' : 'SHORT';
    }
}