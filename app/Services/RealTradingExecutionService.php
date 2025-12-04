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
            ->limit($this->maxUsersPerBatch)
            ->get()
            ->filter(function($user) {
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
        
        foreach (array_chunk($userIds, $this->batchSize) as $batchIndex => $batchUserIds) {
            try {
                ProcessTradeBatch::dispatch($batchUserIds, $decision->id)
                    ->onQueue('trading_batch')
                    ->delay(now()->addSeconds($batchIndex * 2));
                
                $batchesDispatched++;
                
                Log::info("Dispatched batch {$batchIndex} for {$decision->symbol}", [
                    'users' => count($batchUserIds),
                    'delay' => $batchIndex * 2 . ' seconds'
                ]);
                
            } catch (\Exception $e) {
                Log::error("Failed to dispatch batch {$batchIndex}: " . $e->getMessage());
            }
        }
        
        $this->scheduleCleanupJob($decision->symbol, $userIds);
        
        return $batchesDispatched;
    }
    
    /**
     * ✅ Schedule cleanup job setelah semua batch selesai
     */
    private function scheduleCleanupJob(string $symbol, array $userIds): void
    {
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
        if (!$user->portfolio) {
            return ['valid' => false, 'message' => 'User portfolio not found'];
        }
        
        $portfolio = $user->portfolio;
        
        if (!$portfolio->real_trading_enabled || !$portfolio->real_trading_active) {
            return ['valid' => false, 'message' => 'Trading not enabled'];
        }
        
        $cachedBalance = $this->tradingCache->getBalance($user->id);
        $balance = $cachedBalance['total'] ?? $portfolio->real_balance;
        
        if ($balance < 11) {
            return ['valid' => false, 'message' => 'Insufficient balance (minimum $11 required)'];
        }
        
        if (!$user->binanceAccounts()->active()->verified()->exists()) {
            return ['valid' => false, 'message' => 'No active Binance account'];
        }
        
        $rateLimit = $this->tradingCache->limitUserApiCall($user->id);
        if (!$rateLimit['allowed']) {
            return [
                'valid' => false, 
                'message' => 'Rate limit exceeded. Please wait before trading again.',
                'retry_after' => $rateLimit['retry_after']
            ];
        }
        
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
     * ✅✅✅ Execute trade dengan Stop Loss & Take Profit - FIXED VERSION
     */
    private function executeTradeWithSLTP(User $user, AiDecision $decision): array
    {
        try {
            // 1. Get Binance instance
            $binance = $this->binanceAccountService->getBinanceInstance($user->id);
            
            // 2. Get balance
            $balance = $this->getUserBalance($user->id, $binance);
            
            // 3. Determine position type
            $positionType = $this->getPositionTypeFromAction($decision->action);
            
            // 4. Calculate position size
            $positionSize = $this->calculatePositionSize($balance, $decision->price, $positionType);
            
            // 5. Set leverage
            $this->setLeverage($binance, $decision->symbol, $this->leverage);
            
            // 6. Calculate SL/TP prices
            $stopLossPrice = $this->calculateStopLossPrice($decision->price, $positionType);
            $takeProfitPrice = $this->calculateTakeProfitPrice($decision->price, $positionType);
            
            Log::info("📊 Trade parameters for user {$user->id}", [
                'symbol' => $decision->symbol,
                'position_type' => $positionType,
                'entry_price' => $decision->price,
                'quantity' => $positionSize['quantity'],
                'stop_loss' => $stopLossPrice,
                'take_profit' => $takeProfitPrice,
                'leverage' => $this->leverage
            ]);
            
            // 7. Place MAIN LIMIT order (order pembuka - tanpa reduceOnly)
            $mainOrder = $this->placeMainOrder(
                $binance,
                $decision->symbol,
                $positionType,
                $positionSize['quantity'],
                $decision->price
            );
            
            if (!$mainOrder['success']) {
                throw new \Exception($mainOrder['message']);
            }
            
            $mainOrderId = $mainOrder['order_id'];
            
            // 8. Place OCO Order (Stop Loss + Take Profit dalam satu paket)
            $ocoResult = $this->placeOCOOrders(
                $binance,
                $decision->symbol,
                $positionType,
                $positionSize['quantity'],
                $stopLossPrice,
                $takeProfitPrice,
                $mainOrderId
            );
            
            // 9. Save to database
            $pendingOrder = $this->savePendingOrder(
                $user->id,
                $decision,
                $positionType,
                $positionSize,
                $mainOrderId,
                $ocoResult['stop_loss_order_id'] ?? null,
                $ocoResult['take_profit_order_id'] ?? null,
                $stopLossPrice,
                $takeProfitPrice
            );
            
            Log::info("✅ Trade executed successfully for user {$user->id}", [
                'symbol' => $decision->symbol,
                'order_id' => $mainOrderId,
                'amount' => $positionSize['amount'],
                'quantity' => $positionSize['quantity'],
                'sl_order_id' => $ocoResult['stop_loss_order_id'] ?? null,
                'tp_order_id' => $ocoResult['take_profit_order_id'] ?? null
            ]);
            
            return [
                'success' => true,
                'message' => 'Trade executed successfully',
                'order_id' => $mainOrderId,
                'pending_order_id' => $pendingOrder->id,
                'symbol' => $decision->symbol,
                'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
                'position_type' => $positionType,
                'quantity' => $positionSize['quantity'],
                'amount' => $positionSize['amount'],
                'entry_price' => $decision->price,
                'stop_loss' => $stopLossPrice,
                'take_profit' => $takeProfitPrice,
                'sl_order_id' => $ocoResult['stop_loss_order_id'] ?? null,
                'tp_order_id' => $ocoResult['take_profit_order_id'] ?? null,
                'leverage' => $this->leverage
            ];
            
        } catch (\Exception $e) {
            Log::error("❌ Trade execution failed for user {$user->id}: " . $e->getMessage());
            
            // Try to cancel any orders that might have been placed
            if (isset($binance) && isset($decision)) {
                $this->cleanupFailedOrders($binance, $decision->symbol, $mainOrderId ?? null);
            }
            
            throw new \Exception("Trade execution failed: " . $e->getMessage());
        }
    }
    
    /**
     * ✅ Place MAIN order (LIMIT order pembuka posisi)
     */
    private function placeMainOrder($binance, string $symbol, string $positionType, float $quantity, float $price): array
    {
        try {
            $side = $positionType === 'LONG' ? 'BUY' : 'SELL';
            
            $order = $binance->futuresNewOrder($symbol, $side, 'LIMIT', [
                'quantity' => $quantity,
                'price' => $price,
                'timeInForce' => 'GTC'
            ]);
            
            if (!isset($order['orderId'])) {
                throw new \Exception("Main order failed: " . json_encode($order));
            }
            
            return [
                'success' => true,
                'order_id' => $order['orderId'],
                'status' => $order['status'] ?? 'NEW'
            ];
            
        } catch (\Exception $e) {
            Log::error("Main order placement failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * ✅ Place OCO Orders (Stop Loss + Take Profit dalam satu paket) - RECOMMENDED
     */
    private function placeOCOOrders($binance, string $symbol, string $positionType, float $quantity, 
                                   float $stopLossPrice, float $takeProfitPrice, string $mainOrderId): array
    {
        try {
            // Tentukan sisi order untuk SL/TP (selalu berlawanan dengan posisi)
            $side = $positionType === 'LONG' ? 'SELL' : 'BUY';
            
            // Buat OCO order
            $ocoOrder = $binance->futuresNewOrder($symbol, $side, 'OCO', [
                'quantity' => $quantity,
                'price' => $takeProfitPrice, // Price untuk LIMIT (Take Profit)
                'stopPrice' => $stopLossPrice, // Stop price untuk STOP_MARKET
                'stopLimitPrice' => $stopLossPrice, // Harga limit untuk stop (sama dengan stopPrice untuk MARKET)
                'stopLimitTimeInForce' => 'GTC',
                'listClientOrderId' => 'oco_' . $mainOrderId
            ]);
            
            // Parse response OCO
            $orderListId = $ocoOrder['orderListId'] ?? null;
            
            if (!$orderListId) {
                throw new \Exception("OCO order failed: " . json_encode($ocoOrder));
            }
            
            // Ambil order IDs dari OCO response
            $orders = $ocoOrder['orderReports'] ?? [];
            $stopLossOrderId = null;
            $takeProfitOrderId = null;
            
            foreach ($orders as $orderReport) {
                if ($orderReport['type'] === 'STOP_MARKET' || $orderReport['type'] === 'STOP') {
                    $stopLossOrderId = $orderReport['orderId'] ?? null;
                } elseif ($orderReport['type'] === 'LIMIT') {
                    $takeProfitOrderId = $orderReport['orderId'] ?? null;
                }
            }
            
            Log::info("OCO order placed successfully", [
                'order_list_id' => $orderListId,
                'sl_order_id' => $stopLossOrderId,
                'tp_order_id' => $takeProfitOrderId,
                'symbol' => $symbol
            ]);
            
            return [
                'success' => true,
                'order_list_id' => $orderListId,
                'stop_loss_order_id' => $stopLossOrderId,
                'take_profit_order_id' => $takeProfitOrderId
            ];
            
        } catch (\Exception $e) {
            Log::error("OCO order placement failed: " . $e->getMessage());
            
            // Fallback: Place separate orders jika OCO gagal
            return $this->placeSeparateSLTPOrders($binance, $symbol, $positionType, $quantity, $stopLossPrice, $takeProfitPrice);
        }
    }
    
    /**
     * ✅ Fallback: Place separate SL/TP orders jika OCO gagal
     */
    private function placeSeparateSLTPOrders($binance, string $symbol, string $positionType, float $quantity,
                                           float $stopLossPrice, float $takeProfitPrice): array
    {
        $side = $positionType === 'LONG' ? 'SELL' : 'BUY';
        $stopLossOrderId = null;
        $takeProfitOrderId = null;
        
        try {
            // Place Stop Loss (STOP_MARKET dengan reduceOnly)
            $slOrder = $binance->futuresNewOrder($symbol, $side, 'STOP_MARKET', [
                'quantity' => $quantity,
                'stopPrice' => $stopLossPrice,
                'reduceOnly' => 'true',
                'workingType' => 'MARK_PRICE'
            ]);
            
            $stopLossOrderId = $slOrder['orderId'] ?? null;
            Log::info("Separate Stop Loss order placed", ['order_id' => $stopLossOrderId]);
            
        } catch (\Exception $e) {
            Log::error("Separate Stop Loss order failed: " . $e->getMessage());
        }
        
        try {
            // Place Take Profit (LIMIT dengan reduceOnly)
            $tpOrder = $binance->futuresNewOrder($symbol, $side, 'LIMIT', [
                'quantity' => $quantity,
                'price' => $takeProfitPrice,
                'timeInForce' => 'GTC',
                'reduceOnly' => 'true'
            ]);
            
            $takeProfitOrderId = $tpOrder['orderId'] ?? null;
            Log::info("Separate Take Profit order placed", ['order_id' => $takeProfitOrderId]);
            
        } catch (\Exception $e) {
            Log::error("Separate Take Profit order failed: " . $e->getMessage());
        }
        
        return [
            'success' => ($stopLossOrderId || $takeProfitOrderId),
            'stop_loss_order_id' => $stopLossOrderId,
            'take_profit_order_id' => $takeProfitOrderId
        ];
    }
    
    /**
     * ✅ Cleanup failed orders jika execution gagal
     */
    private function cleanupFailedOrders($binance, string $symbol, ?string $orderId = null): void
    {
        try {
            if ($orderId) {
                $binance->futuresCancel($symbol, $orderId);
                Log::info("Cleaned up failed order", ['order_id' => $orderId]);
            }
            
            // Juga coba cancel open orders untuk symbol ini
            $openOrders = $binance->futuresOpenOrders($symbol);
            foreach ($openOrders as $order) {
                try {
                    $binance->futuresCancel($symbol, $order['orderId']);
                } catch (\Exception $e) {
                    // Ignore cancellation errors during cleanup
                }
            }
        } catch (\Exception $e) {
            // Ignore errors during cleanup
        }
    }
    
    /**
     * ✅ Get user balance dengan cache optimization
     */
    private function getUserBalance(int $userId, $binance): float
    {
        $cachedBalance = $this->tradingCache->getBalance($userId);
        
        if ($cachedBalance && isset($cachedBalance['total'])) {
            $cacheAge = time() - ($cachedBalance['timestamp'] ?? 0);
            if ($cacheAge < 30) {
                return $cachedBalance['total'];
            }
        }
        
        try {
            $balance = $this->fetchBinanceBalance($binance);
            $this->tradingCache->cacheBalance($userId, $balance);
            return $balance['total'];
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch balance for user {$userId}: " . $e->getMessage());
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
            // Coba futures balance terlebih dahulu
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
     * ✅ Calculate position size dengan risk management - IMPROVED
     */
    private function calculatePositionSize(float $balance, float $entryPrice, string $positionType): array
    {
        // Risk amount: 2% dari balance
        $riskAmount = $balance * $this->riskPerTrade;
        
        // Batasan: Min $11, Max $50
        $riskAmount = max(11, min($riskAmount, 50));
        
        // Adjust untuk leverage
        $positionAmount = $riskAmount * $this->leverage;
        
        // Quantity berdasarkan entry price
        $quantity = $positionAmount / $entryPrice;
        
        // Round quantity ke precision yang sesuai
        $quantity = round($quantity, $this->getQuantityPrecision($entryPrice));
        
        // Pastikan quantity minimum terpenuhi
        $minQuantity = $this->getMinQuantity($entryPrice);
        if ($quantity < $minQuantity) {
            $quantity = $minQuantity;
            $positionAmount = $quantity * $entryPrice / $this->leverage;
        }
        
        return [
            'amount' => $positionAmount,
            'quantity' => $quantity,
            'risk_amount' => $riskAmount,
            'risk_percentage' => $this->riskPerTrade * 100,
            'balance_used' => $riskAmount,
            'leverage' => $this->leverage
        ];
    }
    
    /**
     * ✅ Helper: Get quantity precision
     */
    private function getQuantityPrecision(float $price): int
    {
        if ($price >= 1000) return 2;
        if ($price >= 100) return 3;
        if ($price >= 10) return 4;
        if ($price >= 1) return 5;
        return 6;
    }
    
    /**
     * ✅ Helper: Get minimum quantity
     */
    private function getMinQuantity(float $price): float
    {
        // Minimum order value biasanya $10 di Binance Futures
        $minOrderValue = 10;
        $minQuantity = $minOrderValue / $price;
        
        // Round up untuk pastikan melebihi minimum
        return ceil($minQuantity * 10000) / 10000;
    }
    
    /**
     * ✅ Save pending order ke database - IMPROVED
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
            'risk_amount' => $positionSize['risk_amount'] ?? 0,
            'leverage' => $this->leverage,
            'expires_at' => now()->addMinutes($this->orderExpiryMinutes),
            'status' => 'PENDING',
            'notes' => json_encode([
                'risk_percentage' => $positionSize['risk_percentage'] ?? 0,
                'sl_percentage' => $this->stopLossPercentage,
                'tp_percentage' => $this->takeProfitPercentage,
                'entry_time' => now()->toISOString()
            ])
        ]);
    }
    
    /**
     * ✅ Update user trade cache
     */
    private function updateUserTradeCache(int $userId, ?string $orderId): void
    {
        try {
            $this->tradingCache->invalidateUserCache($userId);
            
            $this->tradingCache->setUserState($userId, 'last_trade', [
                'time' => now()->timestamp,
                'order_id' => $orderId
            ], 300);
            
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
                'cancelled_at' => now(),
                'notes' => 'Automatically expired. Cancelled: ' . implode(', ', $cancelled)
            ]);
            
            $this->tradingCache->invalidateUserCache($order->user_id);
            
            return true;
            
        } catch (\Exception $e) {
            $order->update([
                'status' => 'EXPIRED',
                'cancelled_at' => now(),
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
            
            $stopLossPrice = $this->calculateStopLossPrice(
                $order->limit_price ?? $order->avg_price ?? $order->executed_price,
                $order->position_type
            );
            
            $stopLossSide = $order->side === 'BUY' ? 'SELL' : 'BUY';
            
            $stopLossOrder = $binance->futuresNewOrder($order->symbol, $stopLossSide, 'STOP_MARKET', [
                'quantity' => $order->quantity,
                'stopPrice' => $stopLossPrice,
                'reduceOnly' => 'true',
                'workingType' => 'MARK_PRICE'
            ]);
            
            if (!isset($stopLossOrder['orderId'])) {
                throw new \Exception("Stop loss order failed: " . json_encode($stopLossOrder));
            }
            
            $order->update([
                'sl_order_id' => $stopLossOrder['orderId'],
                'stop_loss_price' => $stopLossPrice,
                'notes' => ($order->notes ?? '') . " | Stop loss added post-fill at " . now()->toDateTimeString()
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
            $stats['total_users'] = User::count();
            $stats['active_traders'] = User::whereHas('portfolio', function($q) {
                $q->where('real_trading_enabled', true)
                  ->where('real_trading_active', true);
            })->count();
            
            $stats['total_pending_orders'] = PendingOrder::whereIn('status', ['PENDING', 'PARTIALLY_FILLED'])->count();
            $stats['total_open_positions'] = PendingOrder::where('status', 'FILLED')->count();
            
            $stats['cache_stats'] = $this->tradingCache->getStats();
            
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
            $this->tradingCache->invalidateUserCache($userId);
            
            RefreshUserDataJob::dispatch($userId)
                ->onQueue('trading')
                ->delay(now()->addSeconds(1));
            
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
            // Coba futures leverage terlebih dahulu
            if (method_exists($binance, 'futures_change_leverage')) {
                return $binance->futures_change_leverage($symbol, $leverage);
            } elseif (method_exists($binance, 'change_leverage')) {
                return $binance->change_leverage($symbol, $leverage);
            }
            
            // Fallback ke margin leverage
            if (method_exists($binance, 'futuresLeverage')) {
                return $binance->futuresLeverage($symbol, $leverage);
            }
            
        } catch (\Exception $e) {
            Log::warning("Leverage setting failed (non-critical): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ✅ HELPER: Calculate Stop Loss Price
     */
    private function calculateStopLossPrice($entryPrice, $positionType): float
    {
        $percentage = $this->stopLossPercentage / 100;
        
        if ($positionType === 'LONG') {
            return round($entryPrice * (1 - $percentage), $this->getPricePrecision($entryPrice));
        } else {
            return round($entryPrice * (1 + $percentage), $this->getPricePrecision($entryPrice));
        }
    }
    
    /**
     * ✅ HELPER: Calculate Take Profit Price
     */
    private function calculateTakeProfitPrice($entryPrice, $positionType): float
    {
        $percentage = $this->takeProfitPercentage / 100;
        
        if ($positionType === 'LONG') {
            return round($entryPrice * (1 + $percentage), $this->getPricePrecision($entryPrice));
        } else {
            return round($entryPrice * (1 - $percentage), $this->getPricePrecision($entryPrice));
        }
    }
    
    /**
     * ✅ HELPER: Get price precision
     */
    private function getPricePrecision(float $price): int
    {
        if ($price >= 1000) return 1;
        if ($price >= 100) return 2;
        if ($price >= 10) return 3;
        if ($price >= 1) return 4;
        return 5;
    }
    
    /**
     * ✅ HELPER: Get position type from action
     */
    private function getPositionTypeFromAction($action): string
    {
        $action = strtoupper($action);
        
        if ($action === 'BUY' || $action === 'LONG') {
            return 'LONG';
        } elseif ($action === 'SELL' || $action === 'SHORT') {
            return 'SHORT';
        }
        
        // Default to LONG jika tidak dikenali
        return 'LONG';
    }
}