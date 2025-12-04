<?php
// app/Services/RealTradingExecutionService.php

namespace App\Services;

use App\Models\User;
use App\Models\AiDecision;
use App\Models\PendingOrder;
use App\Models\RealOrder; // Asumsi Anda punya model RealOrder
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
    private $stopLossPercentage = 2.0;
    private $takeProfitPercentage = 4.0;
    private $riskPerTrade = 0.02;
    private $orderExpiryMinutes = 15;
    private $leverage = 5;
    private $batchSize = 25;
    
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
     * ✅ Execute REAL trade dengan queue system
     */
    public function executeRealTrade(AiDecision $decision): array
    {
        Log::info("🚀 REAL TRADING: Starting execution for {$decision->action} {$decision->symbol}");
        
        // 1. Cek rate limiting
        $rateLimit = $this->tradingCache->limitSymbolTrading($decision->symbol);
        if (!$rateLimit['allowed']) {
            Log::warning("Rate limit exceeded for symbol {$decision->symbol}");
            return [
                'success' => false,
                'message' => 'Rate limit exceeded.',
                'retry_after' => $rateLimit['retry_after'] ?? 60,
                'users_processed' => 0
            ];
        }
        
        // 2. Cek jika trade sudah berjalan
        if ($this->tradingCache->isTradeInProgress($decision->symbol)) {
            $progress = $this->tradingCache->getTradeProgress($decision->symbol);
            Log::info("Trade already in progress for {$decision->symbol}", $progress ?? []);
            
            return [
                'success' => false,
                'message' => 'Trade is already being processed.',
                'users_in_progress' => $progress['user_ids'] ?? [],
                'started_at' => $progress['started_at'] ?? null
            ];
        }
        
        // 3. Get eligible users
        $eligibleUsers = $this->getEligibleUsers();
        
        if ($eligibleUsers->isEmpty()) {
            Log::warning("No eligible users for real trading");
            return [
                'success' => false,
                'message' => 'No eligible users found.',
                'users_processed' => 0
            ];
        }
        
        Log::info("🎯 Found {$eligibleUsers->count()} eligible users for {$decision->symbol}");
        
        // 4. Mark trade as in progress
        $this->tradingCache->markTradeInProgress(
            $decision->symbol, 
            $eligibleUsers->pluck('id')->toArray()
        );
        
        // 5. Dispatch jobs dalam batch
        $batchesDispatched = $this->dispatchTradeBatches($eligibleUsers, $decision);
        
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
     * ✅ Get eligible users
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
     * ✅ Dispatch trade jobs
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
        
        // Schedule cleanup
        $this->scheduleCleanupJob($decision->symbol, $userIds);
        
        return $batchesDispatched;
    }
    
    /**
     * ✅ Schedule cleanup job
     */
    private function scheduleCleanupJob(string $symbol, array $userIds): void
    {
        \App\Jobs\CleanupTradeProgress::dispatch($symbol, $userIds)
            ->delay(now()->addMinutes(10))
            ->onQueue('trading');
    }
    
    /**
     * ✅ METHOD SINGLE USER
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
            
            // 4. Execute trade
            $result = DB::transaction(function () use ($user, $decision) {
                return $this->executeTradeWithSLTP($user, $decision);
            });
            
            // 5. Update cache
            if ($result['success']) {
                $this->updateUserTradeCache($user->id, $result['order_id'] ?? null);
            }
            
            // 6. Reset trading state
            $this->tradingCache->setUserTrading($user->id, false);
            
            // 7. Trigger background refresh
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
     * ✅ Validate user
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
                'message' => 'Rate limit exceeded.',
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
     * ✅ Execute trade dengan Stop Loss & Take Profit
     */
    private function executeTradeWithSLTP(User $user, AiDecision $decision): array
    {
        try {
            // 1. Get Binance instance
            $binance = $this->binanceAccountService->getBinanceInstance($user->id);
            
            // 2. Get balance
            $balance = $this->getUserBalance($user->id, $binance);
            
            // 3. Calculate position size
            $positionType = $this->getPositionTypeFromAction($decision->action);
            $positionSize = $this->calculatePositionSize($balance, $decision->price);
            
            // 4. Set leverage
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
                'leverage' => $this->leverage
            ]);
            
            // 6. Place MAIN LIMIT order
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
            
            // 10. Save Real Order (jika diperlukan)
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
                'real_order_id' => $realOrder->id ?? null,
                'amount' => $positionSize['amount'],
                'quantity' => $positionSize['quantity']
            ]);
            
            return [
                'success' => true,
                'message' => 'Trade executed successfully',
                'order_id' => $mainOrderId,
                'pending_order_id' => $pendingOrder->id,
                'real_order_id' => $realOrder->id ?? null,
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
     * ✅ Get user balance
     */
    private function getUserBalance(int $userId, $binance): float
    {
        // Cek cache
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
            // Untuk library binance/php-binance-api
            if (method_exists($binance, 'balances')) {
                $balances = $binance->balances();
                if (isset($balances['USDT'])) {
                    $total = (float) $balances['USDT']['available'];
                    $available = (float) $balances['USDT']['available'];
                }
            }
            // Untuk library jaggedsoft/php-binance-api
            elseif (method_exists($binance, 'account')) {
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
            // Untuk futures
            elseif (method_exists($binance, 'futuresAccountBalance')) {
                $futuresBalance = $binance->futuresAccountBalance();
                foreach ($futuresBalance as $asset) {
                    if ($asset['asset'] === 'USDT') {
                        $total = (float) $asset['balance'];
                        $available = (float) ($asset['availableBalance'] ?? 0);
                        break;
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
     * ✅ Calculate position size
     */
    private function calculatePositionSize(float $balance, float $entryPrice): array
    {
        $riskAmount = $balance * $this->riskPerTrade;
        $riskAmount = max(11, min($riskAmount, 50));
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
            
            // Untuk library binance/php-binance-api
            if (method_exists($binance, 'order')) {
                $order = $binance->order($symbol, $side, $quantity, $price, 'LIMIT');
            }
            // Untuk jaggedsoft/php-binance-api
            elseif (method_exists($binance, 'buy') || method_exists($binance, 'sell')) {
                if ($side === 'BUY') {
                    $order = $binance->buy($symbol, $quantity, $price);
                } else {
                    $order = $binance->sell($symbol, $quantity, $price);
                }
            }
            // Untuk futures
            elseif (method_exists($binance, 'futuresBuy') || method_exists($binance, 'futuresSell')) {
                if ($side === 'BUY') {
                    $order = $binance->futuresBuy($symbol, $quantity, $price, 'LIMIT');
                } else {
                    $order = $binance->futuresSell($symbol, $quantity, $price, 'LIMIT');
                }
            } else {
                throw new \Exception("No suitable order method found");
            }
            
            if (!isset($order['orderId']) && !isset($order['clientOrderId'])) {
                throw new \Exception("Limit order failed: " . json_encode($order));
            }
            
            $orderId = $order['orderId'] ?? $order['clientOrderId'] ?? uniqid();
            
            return [
                'success' => true,
                'order_id' => $orderId,
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
     * ✅ Place STOP LOSS order
     */
    private function placeStopLossOrder($binance, string $symbol, string $positionType, float $quantity, float $stopPrice): ?string
    {
        try {
            $side = $positionType === 'LONG' ? 'SELL' : 'BUY';
            
            // Untuk futures stop loss
            if (method_exists($binance, 'futuresOrder')) {
                $params = [
                    'type' => 'STOP_MARKET',
                    'stopPrice' => $stopPrice,
                    'closePosition' => true
                ];
                
                $order = $binance->futuresOrder($side, $symbol, $quantity, 0, 'STOP_MARKET', $params);
                return $order['orderId'] ?? null;
            }
            
            // Untuk spot stop loss
            elseif (method_exists($binance, 'order')) {
                $params = [
                    'type' => 'STOP_LOSS_LIMIT',
                    'stopPrice' => $stopPrice,
                    'timeInForce' => 'GTC'
                ];
                
                $order = $binance->order($symbol, $side, $quantity, 0, 'STOP_LOSS_LIMIT', $params);
                return $order['orderId'] ?? null;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::warning("Stop loss order failed: " . $e->getMessage());
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
            
            // Untuk futures take profit
            if (method_exists($binance, 'futuresOrder')) {
                $params = [
                    'type' => 'TAKE_PROFIT_LIMIT',
                    'stopPrice' => $takeProfitPrice,
                    'price' => $takeProfitPrice,
                    'timeInForce' => 'GTC',
                    'reduceOnly' => true
                ];
                
                $order = $binance->futuresOrder($side, $symbol, $quantity, $takeProfitPrice, 'TAKE_PROFIT_LIMIT', $params);
                return $order['orderId'] ?? null;
            }
            
            // Untuk spot take profit
            elseif (method_exists($binance, 'order')) {
                $params = [
                    'type' => 'TAKE_PROFIT_LIMIT',
                    'stopPrice' => $takeProfitPrice,
                    'price' => $takeProfitPrice,
                    'timeInForce' => 'GTC'
                ];
                
                $order = $binance->order($symbol, $side, $quantity, $takeProfitPrice, 'TAKE_PROFIT_LIMIT', $params);
                return $order['orderId'] ?? null;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::warning("Take profit order failed: " . $e->getMessage());
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
     * ✅ Save real order ke database (jika ada model RealOrder)
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
            return \App\Models\RealOrder::create([
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
            'checked' => 0,
            'cancelled_all' => 0,
            'failed' => 0
        ];
        
        try {
            $expiredOrders = PendingOrder::where('status', 'PENDING')
                ->where('expires_at', '<=', now())
                ->get();
            
            $results['checked'] = $expiredOrders->count();
            
            foreach ($expiredOrders as $order) {
                try {
                    if ($this->cancelExpiredOrder($order)) {
                        $results['cancelled_all']++;
                        
                        Log::info("Order cancelled", [
                            'order_id' => $order->id,
                            'user_id' => $order->user_id,
                            'symbol' => $order->symbol
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
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($order->user_id);
            
            // Cancel order di Binance
            if (method_exists($binance, 'cancel')) {
                $binance->cancel($order->symbol, $order->binance_order_id);
            } elseif (method_exists($binance, 'futuresCancel')) {
                $binance->futuresCancel($order->symbol, $order->binance_order_id);
            }
            
            // Cancel SL & TP jika ada
            if ($order->sl_order_id) {
                try {
                    if (method_exists($binance, 'cancel')) {
                        $binance->cancel($order->symbol, $order->sl_order_id);
                    }
                } catch (\Exception $e) {
                    // Ignore jika gagal
                }
            }
            
            if ($order->take_profit_order_id) {
                try {
                    if (method_exists($binance, 'cancel')) {
                        $binance->cancel($order->symbol, $order->take_profit_order_id);
                    }
                } catch (\Exception $e) {
                    // Ignore jika gagal
                }
            }
            
            // Update database
            $order->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
                'notes' => 'Automatically cancelled after expiry'
            ]);
            
            // Invalidate cache
            $this->tradingCache->invalidateUserCache($order->user_id);
            
            Log::info("✅ Order {$order->id} cancelled", [
                'user_id' => $order->user_id,
                'symbol' => $order->symbol
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("❌ Failed to cancel order {$order->id}: " . $e->getMessage());
            
            $order->update([
                'status' => 'EXPIRED',
                'cancelled_at' => now(),
                'notes' => 'Expired (cancel failed: ' . $e->getMessage() . ')'
            ]);
            
            return false;
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
            } elseif (method_exists($binance, 'changeLeverage')) {
                $binance->changeLeverage($symbol, $leverage);
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
    
    /**
     * ✅ NEW: Update order status dari Binance
     */
    public function syncOrderStatus(int $userId, string $orderId): array
    {
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($userId);
            
            // Cari order di database
            $pendingOrder = PendingOrder::where('user_id', $userId)
                ->where('binance_order_id', $orderId)
                ->first();
            
            if (!$pendingOrder) {
                return ['success' => false, 'message' => 'Order not found'];
            }
            
            // Get order status dari Binance
            $orderStatus = null;
            if (method_exists($binance, 'orderStatus')) {
                $orderStatus = $binance->orderStatus($pendingOrder->symbol, $orderId);
            } elseif (method_exists($binance, 'futuresGetOrder')) {
                $orderStatus = $binance->futuresGetOrder($pendingOrder->symbol, $orderId);
            }
            
            if (!$orderStatus) {
                return ['success' => false, 'message' => 'Failed to fetch order status'];
            }
            
            // Update status di database
            $status = $orderStatus['status'] ?? 'UNKNOWN';
            $executedQty = $orderStatus['executedQty'] ?? 0;
            $avgPrice = $orderStatus['avgPrice'] ?? 0;
            
            $pendingOrder->update([
                'status' => $status,
                'executed_quantity' => $executedQty,
                'avg_price' => $avgPrice,
                'last_sync' => now()
            ]);
            
            // Jika order filled, update real order jika ada
            if ($status === 'FILLED' && class_exists('App\Models\RealOrder')) {
                $this->updateRealOrder($userId, $orderId, $orderStatus);
            }
            
            return [
                'success' => true,
                'status' => $status,
                'executed_quantity' => $executedQty,
                'avg_price' => $avgPrice
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to sync order status for user {$userId}, order {$orderId}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * ✅ Update real order setelah filled
     */
    private function updateRealOrder(int $userId, string $orderId, array $orderStatus): void
    {
        try {
            $realOrder = \App\Models\RealOrder::where('user_id', $userId)
                ->where('binance_order_id', $orderId)
                ->first();
            
            if ($realOrder) {
                $realOrder->update([
                    'status' => 'FILLED',
                    'executed_quantity' => $orderStatus['executedQty'] ?? 0,
                    'executed_price' => $orderStatus['avgPrice'] ?? 0,
                    'filled_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to update real order: " . $e->getMessage());
        }
    }
}