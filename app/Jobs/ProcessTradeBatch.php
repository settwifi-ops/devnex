<?php
// app/Jobs/ProcessTradeBatch.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Cache\TradingCacheService;
use App\Services\BinanceAccountService;
use App\Models\User;
use App\Models\AiDecision;
use App\Models\PendingOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessTradeBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userIds;
    public $decisionId;
    public $timeout = 600; // 10 menit timeout
    public $tries = 3;
    public $backoff = [60, 300, 600];
    public $maxExceptions = 3;

    public function __construct(array $userIds, int $decisionId)
    {
        $this->userIds = $userIds;
        $this->decisionId = $decisionId;
    }

    public function handle()
    {
        Log::info("🚀 Processing trade batch for decision {$this->decisionId}", [
            'user_count' => count($this->userIds),
            'user_ids' => $this->userIds
        ]);
        
        try {
            $decision = AiDecision::find($this->decisionId);
            
            if (!$decision) {
                Log::error("Decision {$this->decisionId} not found");
                return;
            }
            
            $cache = new TradingCacheService();
            $binanceService = new BinanceAccountService();
            
            $successCount = 0;
            $failedCount = 0;
            
            // Process each user in batch
            foreach ($this->userIds as $userId) {
                try {
                    // Rate limiting per user
                    $rateLimit = $cache->limitUserApiCall($userId);
                    if (!$rateLimit['allowed']) {
                        Log::warning("Rate limit exceeded for user {$userId}, skipping");
                        $failedCount++;
                        continue;
                    }
                    
                    // Mark user as trading
                    $cache->setUserTrading($userId, true);
                    
                    // Execute trade
                    $executed = $this->executeTradeForUser(
                        $userId, 
                        $decision, 
                        $binanceService, 
                        $cache
                    );
                    
                    if ($executed) {
                        $successCount++;
                        
                        // Update cache bahwa user punya order baru
                        $this->updateUserOrderCache($userId, $cache);
                    } else {
                        $failedCount++;
                    }
                    
                    // Unmark user trading
                    $cache->setUserTrading($userId, false);
                    
                } catch (\Exception $e) {
                    Log::error("Trade execution failed for user {$userId}: " . $e->getMessage());
                    $failedCount++;
                    
                    // Reset trading state
                    try {
                        $cache->setUserTrading($userId, false);
                    } catch (\Exception $e2) {
                        Log::error("Failed to reset trading state for user {$userId}: " . $e2->getMessage());
                    }
                }
                
                // Small delay antara user untuk menghindari rate limit
                if (count($this->userIds) > 1) {
                    usleep(100000); // 100ms delay
                }
            }
            
            Log::info("✅ Trade batch completed for decision {$this->decisionId}", [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'symbol' => $decision->symbol,
                'action' => $decision->action
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Trade batch failed for decision {$this->decisionId}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Execute trade for single user
     */
    private function executeTradeForUser(
        int $userId, 
        AiDecision $decision, 
        BinanceAccountService $binanceService,
        TradingCacheService $cache
    ): bool {
        return DB::transaction(function () use ($userId, $decision, $binanceService, $cache) {
            
            // 1. Cek duplicate pending order
            $existingOrder = PendingOrder::where('user_id', $userId)
                ->where('symbol', $decision->symbol)
                ->whereIn('status', ['PENDING', 'PARTIALLY_FILLED', 'NEW'])
                ->first();
            
            if ($existingOrder) {
                Log::info("⏸️ User {$userId} already has pending order for {$decision->symbol}");
                return false;
            }
            
            // 2. Validasi user eligibility
            $user = User::with('portfolio')->find($userId);
            if (!$user || !$user->portfolio) {
                Log::warning("User {$userId} or portfolio not found");
                return false;
            }
            
            $portfolio = $user->portfolio;
            if (!$portfolio->real_trading_enabled || !$portfolio->real_trading_active) {
                Log::warning("User {$userId} trading not enabled");
                return false;
            }
            
            // 3. Validasi balance
            $balance = $this->getUserBalance($userId, $binanceService, $cache);
            if ($balance < 11) { // Minimum balance
                Log::warning("User {$userId} insufficient balance: {$balance}");
                return false;
            }
            
            // 4. Get Binance instance
            $binance = $binanceService->getBinanceInstance($userId);
            
            // 5. Calculate position size (2% risk per trade)
            $riskAmount = $balance * 0.02;
            $riskAmount = max(11, min($riskAmount, 50)); // Min $11, Max $50
            
            if ($riskAmount > $balance) {
                throw new \Exception("Insufficient balance. Required: \${$riskAmount}, Available: \${$balance}");
            }
            
            // 6. Calculate quantity
            $limitPrice = $decision->price;
            $quantity = $riskAmount / $limitPrice;
            $quantity = $this->calculatePreciseQuantity($binance, $decision->symbol, $quantity);
            
            if ($quantity <= 0) {
                throw new \Exception("Invalid quantity: {$quantity}");
            }
            
            // 7. Set leverage (5x)
            $this->setLeverage($binance, $decision->symbol, 5);
            
            // 8. Calculate Stop Loss & Take Profit
            $positionType = $decision->action === 'BUY' ? 'LONG' : 'SHORT';
            $stopLossPrice = $this->calculateStopLossPrice($limitPrice, $positionType);
            $takeProfitPrice = $this->calculateTakeProfitPrice($limitPrice, $positionType);
            
            // 9. Place LIMIT order
            $order = $binance->futuresOrder(
                $positionType === 'LONG' ? 'BUY' : 'SELL',
                $decision->symbol,
                $quantity,
                $limitPrice,
                'LIMIT',
                [
                    'timeInForce' => 'GTC',
                    'leverage' => 5
                ]
            );
            
            if (!isset($order['orderId'])) {
                throw new \Exception("Limit order placement failed: " . json_encode($order));
            }
            
            $mainOrderId = $order['orderId'];
            
            // 10. Place STOP LOSS order (STOP_MARKET)
            $stopLossOrderId = null;
            try {
                $stopLossSide = $positionType === 'LONG' ? 'SELL' : 'BUY';
                $stopLossOrder = $binance->futuresOrder(
                    $stopLossSide,
                    $decision->symbol,
                    $quantity,
                    0,
                    'STOP_MARKET',
                    [
                        'stopPrice' => $stopLossPrice,
                        'closePosition' => 'true',
                        'reduceOnly' => 'true'
                    ]
                );
                
                $stopLossOrderId = $stopLossOrder['orderId'] ?? null;
            } catch (\Exception $e) {
                Log::warning("Stop loss order failed for user {$userId}: " . $e->getMessage());
            }
            
            // 11. Place TAKE PROFIT order (LIMIT)
            $takeProfitOrderId = null;
            try {
                $takeProfitSide = $positionType === 'LONG' ? 'SELL' : 'BUY';
                $takeProfitOrder = $binance->futuresOrder(
                    $takeProfitSide,
                    $decision->symbol,
                    $quantity,
                    $takeProfitPrice,
                    'LIMIT',
                    [
                        'timeInForce' => 'GTC',
                        'reduceOnly' => 'true'
                    ]
                );
                
                $takeProfitOrderId = $takeProfitOrder['orderId'] ?? null;
            } catch (\Exception $e) {
                Log::warning("Take profit order failed for user {$userId}: " . $e->getMessage());
            }
            
            // 12. Save Pending Order
            PendingOrder::create([
                'user_id' => $userId,
                'ai_decision_id' => $decision->id,
                'symbol' => $decision->symbol,
                'binance_order_id' => $mainOrderId,
                'sl_order_id' => $stopLossOrderId,
                'take_profit_order_id' => $takeProfitOrderId,
                'limit_price' => $limitPrice,
                'stop_loss_price' => $stopLossPrice,
                'take_profit_price' => $takeProfitPrice,
                'quantity' => $quantity,
                'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
                'position_type' => $positionType,
                'expires_at' => now()->addMinutes(15),
                'status' => 'PENDING',
                'status' => 'NEW',
                'notes' => "Limit order with SL: \${$stopLossPrice}, TP: \${$takeProfitPrice} (Batch Process)"
            ]);
            
            Log::info("✅ Trade executed for user {$userId}", [
                'symbol' => $decision->symbol,
                'side' => $positionType,
                'quantity' => $quantity,
                'amount' => $riskAmount,
                'order_id' => $mainOrderId
            ]);
            
            return true;
        });
    }
    
    /**
     * Get user balance with cache
     */
    private function getUserBalance(
        int $userId, 
        BinanceAccountService $binanceService,
        TradingCacheService $cache
    ): float {
        // Coba dari cache dulu
        $cachedBalance = $cache->getBalance($userId);
        if ($cachedBalance && isset($cachedBalance['total'])) {
            return $cachedBalance['total'];
        }
        
        // Jika tidak ada di cache, fetch dari Binance
        $binance = $binanceService->getBinanceInstance($userId);
        $balance = $this->fetchBinanceBalance($binance);
        
        // Cache hasilnya
        $cache->cacheBalance($userId, $balance);
        
        return $balance['total'] ?? 0;
    }
    
    /**
     * Fetch balance dari Binance
     */
    private function fetchBinanceBalance($binance): array
    {
        try {
            if (method_exists($binance, 'futuresAccountBalance')) {
                $futuresBalance = $binance->futuresAccountBalance();
                
                foreach ($futuresBalance as $asset) {
                    if (isset($asset['asset']) && $asset['asset'] === 'USDT') {
                        return [
                            'total' => (float) $asset['balance'],
                            'available' => (float) ($asset['availableBalance'] ?? 0),
                            'timestamp' => now()->timestamp
                        ];
                    }
                }
            }
            
            // Fallback
            return ['total' => 0, 'available' => 0, 'timestamp' => now()->timestamp];
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch Binance balance: " . $e->getMessage());
            return ['total' => 0, 'available' => 0, 'timestamp' => now()->timestamp];
        }
    }
    
    /**
     * Calculate precise quantity berdasarkan lot size
     */
    private function calculatePreciseQuantity($binance, $symbol, $quantity): float
    {
        try {
            if (!method_exists($binance, 'futuresExchangeInfo')) {
                return round($quantity, 6);
            }
            
            $info = $binance->futuresExchangeInfo();
            
            foreach ($info['symbols'] as $s) {
                if ($s['symbol'] === $symbol) {
                    foreach ($s['filters'] as $filter) {
                        if ($filter['filterType'] === 'LOT_SIZE') {
                            $stepSize = floatval($filter['stepSize']);
                            $minQty = floatval($filter['minQty']);
                            $maxQty = floatval($filter['maxQty']);
                            
                            $precision = strlen(substr(strrchr($stepSize, '.'), 1)) - 1;
                            $adjustedQty = floor($quantity / $stepSize) * $stepSize;
                            $adjustedQty = round($adjustedQty, $precision);
                            
                            if ($adjustedQty < $minQty) {
                                $adjustedQty = $minQty;
                            }
                            if ($adjustedQty > $maxQty) {
                                $adjustedQty = $maxQty;
                            }
                            
                            return $adjustedQty;
                        }
                    }
                    break;
                }
            }
            
            return round($quantity, 6);
            
        } catch (\Exception $e) {
            Log::warning("Quantity precision adjustment failed: " . $e->getMessage());
            return round($quantity, 6);
        }
    }
    
    /**
     * Set leverage
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
     * Calculate Stop Loss Price
     */
    private function calculateStopLossPrice($entryPrice, $positionType): float
    {
        if ($positionType === 'LONG') {
            return $entryPrice * 0.98; // 2% stop loss
        } else {
            return $entryPrice * 1.02; // 2% stop loss
        }
    }
    
    /**
     * Calculate Take Profit Price
     */
    private function calculateTakeProfitPrice($entryPrice, $positionType): float
    {
        if ($positionType === 'LONG') {
            return $entryPrice * 1.04; // 4% take profit
        } else {
            return $entryPrice * 0.96; // 4% take profit
        }
    }
    
    /**
     * Update user order cache
     */
    private function updateUserOrderCache(int $userId, TradingCacheService $cache): void
    {
        try {
            $orders = PendingOrder::where('user_id', $userId)
                ->whereIn('status', ['PENDING', 'PARTIALLY_FILLED', 'NEW', 'FILLED'])
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'symbol' => $order->symbol,
                        'side' => $order->side,
                        'position_type' => $order->position_type,
                        'limit_price' => $order->limit_price,
                        'quantity' => $order->quantity,
                        'status' => $order->status,
                        'status' => $order->status,
                        'created_at' => $order->created_at->timestamp,
                        'expires_at' => $order->expires_at ? $order->expires_at->timestamp : null
                    ];
                })
                ->toArray();
            
            $cache->cacheOrders($userId, $orders);
            
        } catch (\Exception $e) {
            Log::warning("Failed to update order cache for user {$userId}: " . $e->getMessage());
        }
    }
    
    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error("❌ ProcessTradeBatch failed for decision {$this->decisionId}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'user_ids' => $this->userIds
        ]);
        
        // Reset trading state untuk semua user
        try {
            $cache = new TradingCacheService();
            foreach ($this->userIds as $userId) {
                $cache->setUserTrading($userId, false);
            }
        } catch (\Exception $e) {
            Log::error("Failed to reset user trading states: " . $e->getMessage());
        }
    }
}