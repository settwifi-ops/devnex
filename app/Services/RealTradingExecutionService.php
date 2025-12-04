<?php
// app/Services/RealTradingExecutionService.php

namespace App\Services;

use App\Models\User;
use App\Models\AiDecision;
use App\Models\PendingOrder;
use App\Models\RealOrder;
use App\Services\Cache\TradingCacheService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
     * ✅ Execute REAL trade untuk semua eligible users
     */
    public function executeRealTrade(AiDecision $decision): array
    {
        Log::info("🚀 REAL TRADING: Starting execution for {$decision->action} {$decision->symbol}");
        
        try {
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
            
            // 5. Execute trades satu per satu (lebih reliable daripada batch)
            $results = $this->executeTradesSequentially($eligibleUsers, $decision);
            
            // 6. Clear trade progress dari cache
            $this->tradingCache->clearTradeProgress($decision->symbol);
            
            // 7. Return hasil
            $successCount = collect($results)->where('success', true)->count();
            $failCount = collect($results)->where('success', false)->count();
            
            return [
                'success' => $successCount > 0,
                'message' => "Executed trades for {$successCount} users, {$failCount} failed",
                'total_users' => $eligibleUsers->count(),
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'results' => $results,
                'symbol' => $decision->symbol,
                'action' => $decision->action
            ];
            
        } catch (\Exception $e) {
            Log::error("Real trade execution failed: " . $e->getMessage());
            
            // Clear progress jika ada error
            $this->tradingCache->clearTradeProgress($decision->symbol ?? 'unknown');
            
            return [
                'success' => false,
                'message' => 'Trade execution failed: ' . $e->getMessage(),
                'error' => get_class($e)
            ];
        }
    }
    
    /**
     * ✅ Execute trades secara sequential untuk menghindari rate limit
     */
    private function executeTradesSequentially($users, AiDecision $decision): array
    {
        $results = [];
        
        foreach ($users as $user) {
            try {
                // Small delay antara user untuk menghindari rate limit
                if (!empty($results)) {
                    usleep(500000); // 0.5 detik delay
                }
                
                $result = $this->executeForUser($user, $decision);
                $results[] = $result;
                
                Log::info("Trade execution result for user {$user->id}: " . ($result['success'] ? 'SUCCESS' : 'FAILED'));
                
            } catch (\Exception $e) {
                Log::error("Failed to execute trade for user {$user->id}: " . $e->getMessage());
                
                $results[] = [
                    'success' => false,
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                    'error' => get_class($e)
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * ✅ Get eligible users dengan optimasi query
     */
    private function getEligibleUsers()
    {
        try {
            $users = User::query()
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
                ->get();
            
            // Filter tambahan di PHP
            return $users->filter(function($user) {
                if (!$user->portfolio) {
                    return false;
                }
                
                // Cek cache untuk rate limiting
                if ($this->tradingCache->isUserTrading($user->id)) {
                    Log::info("User {$user->id} is currently trading, skipping");
                    return false;
                }
                
                return true;
            });
            
        } catch (\Exception $e) {
            Log::error("Failed to get eligible users: " . $e->getMessage());
            return collect();
        }
    }
    
    /**
     * ✅ Execute trade untuk single user
     */
    public function executeForUser(User $user, AiDecision $decision): array
    {
        Log::info("👤 Executing trade for user {$user->id} - {$decision->symbol}");
        
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
                Log::warning("Duplicate order found for user {$user->id} symbol {$decision->symbol}");
                return [
                    'success' => false,
                    'message' => 'User already has pending order for this symbol',
                    'user_id' => $user->id,
                    'symbol' => $decision->symbol
                ];
            }
            
            // 3. Mark user as trading
            $this->tradingCache->setUserTrading($user->id, true);
            
            // 4. Execute trade
            $result = DB::transaction(function () use ($user, $decision) {
                return $this->executeTradeWithSLTP($user, $decision);
            });
            
            // 5. Update cache dengan hasil trade
            if ($result['success']) {
                $this->updateUserTradeCache($user->id, $result['order_id'] ?? null);
            }
            
            return array_merge($result, ['user_id' => $user->id]);
            
        } catch (\Exception $e) {
            Log::error("Trade execution failed for user {$user->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Trade execution failed: ' . $e->getMessage(),
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ];
        } finally {
            // 6. Reset trading state
            $this->tradingCache->setUserTrading($user->id, false);
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
        
        // 3. Cek balance
        $balance = $portfolio->real_balance;
        
        if ($balance < 11) {
            return ['valid' => false, 'message' => 'Insufficient balance (minimum $11 required)'];
        }
        
        // 4. Cek Binance account
        if (!$user->binanceAccounts()->active()->verified()->exists()) {
            return ['valid' => false, 'message' => 'No active Binance account'];
        }
        
        // 5. Cek jika user sedang trading
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
            
            if (!$binance) {
                throw new \Exception("Failed to get Binance instance");
            }
            
            // 2. Get balance
            $balance = $this->getUserBalance($user->id, $binance);
            
            if ($balance < 11) {
                throw new \Exception("Insufficient balance: {$balance} USDT");
            }
            
            // 3. Calculate position size
            $positionType = $this->getPositionTypeFromAction($decision->action);
            $positionSize = $this->calculatePositionSize($balance, $decision->price);
            
            // 4. Set leverage (untuk futures)
            $this->setLeverage($binance, $decision->symbol, $this->leverage);
            
            // 5. Calculate SL/TP prices
            $stopLossPrice = $this->calculateStopLossPrice($decision->price, $positionType);
            $takeProfitPrice = $this->calculateTakeProfitPrice($decision->price, $positionType);
            
            Log::info("📊 Trading parameters for user {$user->id}", [
                'symbol' => $decision->symbol,
                'position_type' => $positionType,
                'entry_price' => $decision->price,
                'quantity' => $positionSize['quantity'],
                'stop_loss' => $stopLossPrice,
                'take_profit' => $takeProfitPrice,
                'leverage' => $this->leverage,
                'balance' => $balance
            ]);
            
            // 6. Place MAIN LIMIT order (menggunakan jaggedsoft/php-binance-api)
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
            $stopLossOrderId = null;
            try {
                $stopLossOrderId = $this->placeStopLossOrder(
                    $binance,
                    $decision->symbol,
                    $positionType,
                    $positionSize['quantity'],
                    $stopLossPrice
                );
                
                if ($stopLossOrderId) {
                    Log::info("✅ Stop loss order placed for user {$user->id}", [
                        'order_id' => $stopLossOrderId,
                        'stop_price' => $stopLossPrice
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Stop loss order failed for user {$user->id}: " . $e->getMessage());
            }
            
            // 8. Place Take Profit order
            $takeProfitOrderId = null;
            try {
                $takeProfitOrderId = $this->placeTakeProfitOrder(
                    $binance,
                    $decision->symbol,
                    $positionType,
                    $positionSize['quantity'],
                    $takeProfitPrice
                );
                
                if ($takeProfitOrderId) {
                    Log::info("✅ Take profit order placed for user {$user->id}", [
                        'order_id' => $takeProfitOrderId,
                        'take_profit_price' => $takeProfitPrice
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Take profit order failed for user {$user->id}: " . $e->getMessage());
            }
            
            // 9. Save Pending Order
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
            
            // 10. Save Real Order
            $realOrder = $this->saveRealOrder(
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
                'pending_order_id' => $pendingOrder->id,
                'real_order_id' => $realOrder->id,
                'amount' => $positionSize['amount'],
                'quantity' => $positionSize['quantity']
            ]);
            
            return [
                'success' => true,
                'message' => 'Trade executed successfully',
                'order_id' => $mainOrderId,
                'pending_order_id' => $pendingOrder->id,
                'real_order_id' => $realOrder->id,
                'symbol' => $decision->symbol,
                'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
                'quantity' => $positionSize['quantity'],
                'amount' => $positionSize['amount'],
                'stop_loss' => $stopLossPrice,
                'take_profit' => $takeProfitPrice,
                'sl_order_id' => $stopLossOrderId,
                'tp_order_id' => $takeProfitOrderId,
                'expires_at' => $pendingOrder->expires_at->format('Y-m-d H:i:s')
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
     * ✅ Fetch balance dari Binance (jaggedsoft/php-binance-api)
     */
    private function fetchBinanceBalance($binance): array
    {
        $total = 0;
        $available = 0;
        
        try {
            // Method untuk jaggedsoft/php-binance-api
            if (method_exists($binance, 'balances')) {
                $balances = $binance->balances();
                
                if (isset($balances['USDT'])) {
                    $total = (float) $balances['USDT']['available'];
                    $available = (float) $balances['USDT']['available'];
                }
            }
            
            // Jika masih 0, coba method account
            if ($total <= 0 && method_exists($binance, 'account')) {
                $account = $binance->account();
                
                if (isset($account['balances'])) {
                    foreach ($account['balances'] as $balance) {
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
            throw $e;
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
        
        // Round quantity ke 6 decimal places
        $quantity = round($quantity, 6);
        
        return [
            'amount' => $riskAmount,
            'quantity' => $quantity,
            'risk_percentage' => $this->riskPerTrade * 100,
            'balance_used' => $riskAmount
        ];
    }
    
    /**
     * ✅ Place LIMIT order (jaggedsoft/php-binance-api)
     */
    private function placeLimitOrder($binance, string $symbol, string $positionType, float $quantity, float $price): array
    {
        try {
            $side = $positionType === 'LONG' ? 'BUY' : 'SELL';
            
            // Untuk jaggedsoft/php-binance-api
            if ($side === 'BUY') {
                $order = $binance->buy($symbol, $quantity, $price);
            } else {
                $order = $binance->sell($symbol, $quantity, $price);
            }
            
            // Debug response
            Log::debug("Binance order response", ['response' => $order]);
            
            if (!isset($order['orderId']) && !isset($order['clientOrderId'])) {
                throw new \Exception("Limit order failed: " . json_encode($order));
            }
            
            $orderId = $order['orderId'] ?? $order['clientOrderId'];
            
            return [
                'success' => true,
                'order_id' => (string) $orderId,
                'status' => $order['status'] ?? 'NEW',
                'raw_response' => $order
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
     * ✅ Place STOP LOSS order (jaggedsoft/php-binance-api)
     */
    private function placeStopLossOrder($binance, string $symbol, string $positionType, float $quantity, float $stopPrice): ?string
    {
        try {
            $side = $positionType === 'LONG' ? 'SELL' : 'BUY';
            
            // Untuk jaggedsoft/php-binance-api dengan STOP_LOSS_LIMIT
            // Note: Binance spot tidak support STOP_MARKET, jadi pakai STOP_LOSS_LIMIT
            $params = [
                'stopPrice' => $stopPrice,
                'price' => $stopPrice * 0.995, // Slightly below stop price for execution
                'timeInForce' => 'GTC'
            ];
            
            $order = $binance->order($symbol, $side, $quantity, 0, "STOP_LOSS_LIMIT", $params);
            
            return $order['orderId'] ?? $order['clientOrderId'] ?? null;
            
        } catch (\Exception $e) {
            Log::warning("Stop loss order failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ✅ Place TAKE PROFIT order (jaggedsoft/php-binance-api)
     */
    private function placeTakeProfitOrder($binance, string $symbol, string $positionType, float $quantity, float $takeProfitPrice): ?string
    {
        try {
            $side = $positionType === 'LONG' ? 'SELL' : 'BUY';
            
            // Untuk jaggedsoft/php-binance-api dengan TAKE_PROFIT_LIMIT
            $params = [
                'stopPrice' => $takeProfitPrice,
                'timeInForce' => 'GTC'
            ];
            
            $order = $binance->order($symbol, $side, $quantity, $takeProfitPrice, "TAKE_PROFIT_LIMIT", $params);
            
            return $order['orderId'] ?? $order['clientOrderId'] ?? null;
            
        } catch (\Exception $e) {
            Log::warning("Take profit order failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ✅ Save pending order dengan expiration yang jelas
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
        $expiresAt = now()->addMinutes($this->orderExpiryMinutes);
        
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
            'expires_at' => $expiresAt,
            'status' => 'PENDING',
            'notes' => "Auto-cancels at {$expiresAt->format('H:i:s')}"
        ]);
    }
    
    /**
     * ✅ Save real order ke database
     */
    private function saveRealOrder(
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
        // Cek dulu apakah model RealOrder ada
        if (!class_exists('App\Models\RealOrder')) {
            return null;
        }
        
        try {
            return RealOrder::create([
                'user_id' => $userId,
                'ai_decision_id' => $decision->id,
                'symbol' => $decision->symbol,
                'binance_order_id' => $mainOrderId,
                'order_type' => 'LIMIT',
                'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
                'quantity' => $positionSize['quantity'],
                'price' => $decision->price,
                'stop_loss_price' => $stopLossPrice,
                'take_profit_price' => $takeProfitPrice,
                'amount' => $positionSize['amount'],
                'status' => 'NEW',
                'notes' => 'Real trade executed via AI decision'
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to save real order: " . $e->getMessage());
            return null;
        }
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
        Log::info("🕒 Checking expired pending orders for cancellation");
        
        $results = [
            'checked' => 0,
            'cancelled_all' => 0,
            'partially_cancelled' => 0,
            'failed' => 0
        ];
        
        try {
            // Get orders yang sudah expired
            $expiredOrders = PendingOrder::where('status', 'PENDING')
                ->where('expires_at', '<=', now())
                ->with('user')
                ->get();
            
            $results['checked'] = $expiredOrders->count();
            
            foreach ($expiredOrders as $order) {
                try {
                    // Cancel order
                    $cancellationResult = $this->cancelExpiredOrder($order);
                    
                    if ($cancellationResult) {
                        $results['cancelled_all']++;
                        
                        Log::info("📝 Order cancellation completed", [
                            'order_id' => $order->id,
                            'user_id' => $order->user_id,
                            'symbol' => $order->symbol,
                            'expired_at' => $order->expires_at
                        ]);
                    } else {
                        $results['failed']++;
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Failed to process order {$order->id}: " . $e->getMessage());
                    $results['failed']++;
                }
            }
            
            Log::info("✅ Checked expired orders", $results);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error("❌ Check pending orders failed: " . $e->getMessage());
            return $results;
        }
    }
    
    /**
     * ✅ Cancel expired order
     */
    private function cancelExpiredOrder(PendingOrder $order): bool
    {
        DB::beginTransaction();
        
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($order->user_id);
            
            if (!$binance) {
                throw new \Exception("Failed to get Binance instance");
            }
            
            $cancelledOrders = [];
            
            // Cancel main order
            if ($order->binance_order_id) {
                try {
                    $binance->cancel($order->symbol, $order->binance_order_id);
                    $cancelledOrders[] = 'main';
                } catch (\Exception $e) {
                    Log::warning("Failed to cancel main order: " . $e->getMessage());
                }
            }
            
            // Cancel stop loss order
            if ($order->sl_order_id) {
                try {
                    $binance->cancel($order->symbol, $order->sl_order_id);
                    $cancelledOrders[] = 'stop_loss';
                } catch (\Exception $e) {
                    // Order mungkin sudah filled atau cancelled
                }
            }
            
            // Cancel take profit order
            if ($order->take_profit_order_id) {
                try {
                    $binance->cancel($order->symbol, $order->take_profit_order_id);
                    $cancelledOrders[] = 'take_profit';
                } catch (\Exception $e) {
                    // Order mungkin sudah filled atau cancelled
                }
            }
            
            // Update order status di database
            $order->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
                'notes' => 'Automatically cancelled after expiry. Cancelled orders: ' . implode(', ', $cancelledOrders)
            ]);
            
            // Update real order jika ada
            if (class_exists('App\Models\RealOrder')) {
                RealOrder::where('user_id', $order->user_id)
                    ->where('binance_order_id', $order->binance_order_id)
                    ->update([
                        'status' => 'CANCELLED',
                        'cancelled_at' => now()
                    ]);
            }
            
            // Invalidate user cache
            $this->tradingCache->invalidateUserCache($order->user_id);
            
            DB::commit();
            
            Log::info("✅ Order {$order->id} cancelled", [
                'user_id' => $order->user_id,
                'symbol' => $order->symbol,
                'cancelled_orders' => $cancelledOrders
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("❌ Failed to cancel order {$order->id}: " . $e->getMessage());
            
            // Update status meskipun cancel gagal
            $order->update([
                'status' => 'EXPIRED',
                'cancelled_at' => now(),
                'notes' => 'Expired (cancel failed: ' . $e->getMessage() . ')'
            ]);
            
            return false;
        }
    }
    
    /**
     * ✅ HELPER: Set leverage (untuk futures)
     */
    private function setLeverage($binance, $symbol, $leverage)
    {
        try {
            // Untuk futures trading dengan jaggedsoft/php-binance-api
            if (method_exists($binance, 'futures_change_leverage')) {
                $binance->futures_change_leverage($symbol, $leverage);
                Log::info("Leverage set to {$leverage}x for {$symbol}");
            }
        } catch (\Exception $e) {
            Log::warning("Leverage setting failed (might be spot trading): " . $e->getMessage());
        }
    }
    
    /**
     * ✅ HELPER: Calculate Stop Loss Price
     */
    private function calculateStopLossPrice($entryPrice, $positionType): float
    {
        $percentage = $this->stopLossPercentage / 100;
        
        if ($positionType === 'LONG') {
            return round($entryPrice * (1 - $percentage), 6);
        } else {
            return round($entryPrice * (1 + $percentage), 6);
        }
    }
    
    /**
     * ✅ HELPER: Calculate Take Profit Price
     */
    private function calculateTakeProfitPrice($entryPrice, $positionType): float
    {
        $percentage = $this->takeProfitPercentage / 100;
        
        if ($positionType === 'LONG') {
            return round($entryPrice * (1 + $percentage), 6);
        } else {
            return round($entryPrice * (1 - $percentage), 6);
        }
    }
    
    /**
     * ✅ HELPER: Get position type from action
     */
    private function getPositionTypeFromAction($action): string
    {
        return strtoupper($action) === 'BUY' ? 'LONG' : 'SHORT';
    }
    
    /**
     * ✅ METHOD: Get trading statistics
     */
    public function getTradingStatistics(): array
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_traders' => User::whereHas('portfolio', function($q) {
                    $q->where('real_trading_enabled', true)
                      ->where('real_trading_active', true);
                })->count(),
                'total_pending_orders' => PendingOrder::whereIn('status', ['PENDING', 'PARTIALLY_FILLED'])->count(),
                'total_real_orders' => class_exists('App\Models\RealOrder') ? RealOrder::count() : 0,
                'filled_orders' => class_exists('App\Models\RealOrder') ? RealOrder::where('status', 'FILLED')->count() : 0,
                'cache_stats' => $this->tradingCache->getStats() ?? []
            ];
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error("Failed to get trading statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * ✅ METHOD: Force refresh user data
     */
    public function forceRefreshUserData($userId): array
    {
        try {
            // Invalidate semua cache user
            $this->tradingCache->invalidateUserCache($userId);
            
            return [
                'success' => true,
                'message' => 'User data cache invalidated',
                'user_id' => $userId
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
}