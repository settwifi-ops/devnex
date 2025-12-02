<?php
namespace App\Services;

use App\Models\User;
use App\Models\AiDecision;
use App\Models\UserPosition;
use App\Models\TradeHistory;
use App\Models\PendingOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RealTradingExecutionService
{
    private $binanceAccountService;

    public function __construct(BinanceAccountService $binanceAccountService)
    {
        $this->binanceAccountService = $binanceAccountService;
    }

    /**
     * Execute REAL trade dengan enhanced safety
     */
    public function executeRealTrade(AiDecision $decision)
    {
        if (config('app.binance_testnet', true)) {
            Log::info("🔧 REAL TRADING: Running in TESTNET mode");
        }

        $eligibleUsers = User::whereHas('portfolio', function($query) {
            $query->where('real_trading_enabled', true)
                  ->where('real_trading_active', true)
                  ->where('real_balance', '>=', 11);
        })->whereHas('binanceAccounts', function($query) {
            $query->active()->verified();
        })->get();

        Log::info("🎯 Real Trading Execution for {$decision->action} {$decision->symbol} - {$eligibleUsers->count()} eligible users");

        if ($eligibleUsers->count() === 0) {
            Log::warning("❌ No eligible users for real trading");
            return 0;
        }

        $successCount = 0;
        
        foreach ($eligibleUsers as $user) {
            try {
                // ✅ Validasi account sebelum trading
                $this->binanceAccountService->validateAccountForTrading($user->id);
                
                DB::transaction(function () use ($user, $decision, &$successCount) {
                    $executed = $this->executeForUser($user, $decision);
                    if ($executed) {
                        $successCount++;
                        $this->notifyRealTradeExecution(
                            $user->id,
                            $decision->symbol,
                            $decision->action,
                            "Real trade executed: {$decision->action} {$decision->symbol}"
                        );
                    }
                });
            } catch (\Exception $e) {
                Log::error("Real trade execution failed for user {$user->id}: " . $e->getMessage());
                $this->notifyRealTradeError(
                    $user->id,
                    $decision->symbol,
                    $decision->action,
                    "Real trade failed: " . $e->getMessage()
                );
            }
        }

        Log::info("✅ Real Trading: {$successCount}/{$eligibleUsers->count()} users executed successfully");
        return $successCount;
    }

    /**
     * ✅ METHOD BARU: Execute real trade untuk single user
     */
    public function executeForUser(User $user, AiDecision $decision)
    {
        try {
            // Cek duplicate pending order
            $existingOrder = PendingOrder::where('user_id', $user->id)
                ->where('symbol', $decision->symbol)
                ->where('status', 'PENDING')
                ->first();

            if ($existingOrder) {
                Log::info("⏸️ SKIP REAL TRADE - Already have pending order for {$decision->symbol}");
                return false;
            }

            // Validasi account
            $this->binanceAccountService->validateAccountForTrading($user->id);
            
            return DB::transaction(function () use ($user, $decision) {
                $portfolio = $user->portfolio;
                $positionType = $this->getPositionTypeFromAction($decision->action);
                
                return $this->executeRealBuy($user, $portfolio, $decision, $positionType);
            });

        } catch (\Exception $e) {
            Log::error("Real trade execution failed for user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ METHOD BARU: Check pending orders yang expired
     */
    public function checkPendingOrders()
    {
        try {
            $expiredOrders = PendingOrder::where('status', 'PENDING')
                ->where('expires_at', '<=', now())
                ->get();

            Log::info("🕒 Checking expired orders: " . $expiredOrders->count());

            foreach ($expiredOrders as $order) {
                $this->cancelExpiredOrder($order);
            }

            return $expiredOrders->count();
            
        } catch (\Exception $e) {
            Log::error("❌ Check pending orders failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ METHOD BARU: Cancel expired order
     */
    private function cancelExpiredOrder(PendingOrder $pendingOrder)
    {
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($pendingOrder->user_id);
            
            // Cancel order di Binance
            $result = $binance->futuresCancel($pendingOrder->symbol, $pendingOrder->binance_order_id);
            
            // Update status di database
            $pendingOrder->update([
                'status' => 'EXPIRED',
                'notes' => 'Automatically cancelled after 15 minutes'
            ]);

            Log::info("🕒 ORDER EXPIRED: " . $pendingOrder->symbol, [
                'user_id' => $pendingOrder->user_id,
                'order_id' => $pendingOrder->binance_order_id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("❌ Cancel expired order failed: " . $e->getMessage());
            
            // Tetap mark as expired meski cancel gagal
            $pendingOrder->update([
                'status' => 'EXPIRED',
                'notes' => 'Auto expiry (cancel failed: ' . $e->getMessage() . ')'
            ]);
            
            return false;
        }
    }

    /**
     * Enhanced REAL BUY execution - MODIFIED untuk LIMIT ORDER
     */
    private function executeRealBuy(User $user, $portfolio, AiDecision $decision, $positionType)
    {
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($user->id);
            
            // ✅ FIX: Gunakan method yang benar
            $balance = $this->binanceAccountService->getFuturesBalanceOnly($user->id);
            $availableBalance = $balance['available']; // ✅ PAKAI INI
            
            // ✅ Calculate safe position size (5% dari available balance)
            $riskAmount = $availableBalance * 0.05;
            $riskAmount = max(11, min($riskAmount, 50));
            
            if ($riskAmount > $availableBalance) {
                throw new \Exception("Insufficient balance. Required: \${$riskAmount}, Available: \${$availableBalance}");
            }

            // ✅ PAKAI HARGA DECISION, bukan current price
            $limitPrice = $decision->price;

            // ✅ Calculate precise quantity
            $quantity = $riskAmount / $limitPrice;
            $quantity = $this->calculatePreciseQuantity($binance, $decision->symbol, $quantity);

            if ($quantity <= 0) {
                throw new \Exception("Invalid quantity: {$quantity}");
            }

            // ✅ Skip leverage untuk testing
            Log::info("⚠️ Skipping leverage setting for testing");

            Log::info("🎯 PLACING REAL LIMIT ORDER", [
                'user_id' => $user->id,
                'symbol' => $decision->symbol,
                'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
                'quantity' => $quantity,
                'limit_price' => $limitPrice,
                'amount' => $riskAmount,
                'expires' => '15 minutes'
            ]);

            // ✅ FIXED: Parameter yang benar untuk futuresOrder()
            $order = $binance->futuresOrder(
                $positionType === 'LONG' ? 'BUY' : 'SELL', // Side: BUY atau SELL (string)
                $decision->symbol,                         // Symbol
                $quantity,                                 // Quantity
                $limitPrice,                               // Price
                'LIMIT',                                   // Type (string)
                [                                          // Parameters (array)
                    'timeInForce' => 'GTC',
                    'leverage' => 5
                ]
            );

            // ✅ Validate order execution
            if (!isset($order['orderId'])) {
                throw new \Exception("Limit order placement failed: " . json_encode($order));
            }

            // ✅ SIMPAN SEBAGAI PENDING ORDER
            PendingOrder::create([
                'user_id' => $user->id,
                'ai_decision_id' => $decision->id,
                'symbol' => $decision->symbol,
                'binance_order_id' => $order['orderId'],
                'limit_price' => $limitPrice,
                'quantity' => $quantity,
                'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
                'position_type' => $positionType,
                'expires_at' => now()->addMinutes(15),
                'status' => 'PENDING',
                'notes' => 'Limit order - 15 minutes expiry'
            ]);

            Log::info("✅ LIMIT ORDER PLACED - Fill or no fill OK", [
                'user_id' => $user->id,
                'order_id' => $order['orderId'],
                'symbol' => $decision->symbol,
                'price' => $limitPrice,
                'quantity' => $quantity,
                'order_response' => $order
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("REAL BUY execution failed for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get current price dari Binance
     */
    private function getCurrentPrice($binance, $symbol)
    {
        try {
            // ✅ Gunakan method price() dari jaggedsoft library
            return $binance->price($symbol);
        } catch (\Exception $e) {
            Log::error("Price fetch failed for {$symbol}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate precise quantity berdasarkan symbol rules
     */
    private function calculatePreciseQuantity($binance, $symbol, $quantity)
    {
        try {
            // ✅ Get exchange info untuk futures
            $info = $binance->futuresExchangeInfo();
            $symbolInfo = null;
            
            foreach ($info['symbols'] as $s) {
                if ($s['symbol'] === $symbol) {
                    $symbolInfo = $s;
                    break;
                }
            }
            
            if (!$symbolInfo) {
                throw new \Exception("Symbol info not found for {$symbol}");
            }

            // Get LOT_SIZE filter
            $lotSizeFilter = null;
            foreach ($symbolInfo['filters'] as $filter) {
                if ($filter['filterType'] === 'LOT_SIZE') {
                    $lotSizeFilter = $filter;
                    break;
                }
            }
            
            if ($lotSizeFilter) {
                $stepSize = floatval($lotSizeFilter['stepSize']);
                $minQty = floatval($lotSizeFilter['minQty']);
                $maxQty = floatval($lotSizeFilter['maxQty']);
                
                // Adjust quantity to step size
                $precision = strlen(substr(strrchr($stepSize, '.'), 1)) - 1;
                $adjustedQty = floor($quantity / $stepSize) * $stepSize;
                $adjustedQty = round($adjustedQty, $precision);
                
                // Validate min/max
                if ($adjustedQty < $minQty) {
                    throw new \Exception("Quantity too small. Min: {$minQty}, Calculated: {$adjustedQty}");
                }
                if ($adjustedQty > $maxQty) {
                    throw new \Exception("Quantity too large. Max: {$maxQty}, Calculated: {$adjustedQty}");
                }
                
                Log::info("🔢 Quantity adjusted", [
                    'original' => $quantity,
                    'adjusted' => $adjustedQty,
                    'step_size' => $stepSize
                ]);
                
                return $adjustedQty;
            }

            return round($quantity, 6); // Default precision

        } catch (\Exception $e) {
            Log::warning("Quantity precision adjustment failed: " . $e->getMessage());
            return round($quantity, 6); // Fallback
        }
    }

    /**
     * Set leverage untuk symbol
     */
    private function setLeverage($binance, $symbol, $leverage)
    {
        try {
            // ✅ Method yang tersedia di jaggedsoft library
            if (method_exists($binance, 'futures_change_leverage')) {
                $result = $binance->futures_change_leverage($symbol, $leverage);
            } 
            // Coba method lain yang mungkin ada
            elseif (method_exists($binance, 'change_leverage')) {
                $result = $binance->change_leverage($symbol, $leverage);
            }
            // Jika tidak ada, gunakan manual API call
            else {
                Log::info("⚙️ Using manual leverage API call for {$symbol}");
                
                // Manual API call ke Binance
                $timestamp = round(microtime(true) * 1000);
                $params = [
                    'symbol' => $symbol,
                    'leverage' => $leverage,
                    'timestamp' => $timestamp
                ];
                
                $query = http_build_query($params);
                $signature = hash_hmac('sha256', $query, $binance->secret);
                $params['signature'] = $signature;
                
                // Gunakan base URL yang benar
                $baseUrl = $binance->testnet 
                    ? 'https://testnet.binancefuture.com' 
                    : 'https://fapi.binance.com';
                    
                $response = $this->client->post("{$baseUrl}/fapi/v1/leverage", [
                    'headers' => [
                        'X-MBX-APIKEY' => $binance->api_key,
                    ],
                    'form_params' => $params
                ]);
                
                $result = json_decode($response->getBody(), true);
            }
            
            Log::info("🎯 Leverage set to {$leverage}x for {$symbol}", (array)$result);
            return $result;
            
        } catch (\Exception $e) {
            Log::warning("⚠️ Leverage setting failed (continuing anyway): " . $e->getMessage());
            // Continue without leverage change - ini opsional
            return null;
        }
    }

    /**
     * Save real position ke database
     */
    private function saveRealPosition($user, $portfolio, $decision, $positionType, $order, $quantity, $price, $amount)
    {
        try {
            $position = UserPosition::create([
                'user_id' => $user->id,
                'portfolio_id' => $portfolio->id,
                'ai_decision_id' => $decision->id,
                'symbol' => $decision->symbol,
                'position_type' => $positionType,
                'qty' => $quantity,
                'avg_price' => $price,
                'current_price' => $price,
                'investment' => $amount,
                'floating_pnl' => 0,
                'pnl_percentage' => 0,
                'stop_loss' => $this->calculateStopLoss($price, $positionType),
                'take_profit' => $this->calculateTakeProfit($price, $positionType),
                'status' => 'OPEN',
                'is_real_trade' => true,
                'binance_order_id' => $order['orderId'],
                'opened_at' => now(),
            ]);

            // Create trade history
            TradeHistory::create([
                'user_id' => $user->id,
                'ai_decision_id' => $decision->id,
                'position_id' => $position->id,
                'symbol' => $decision->symbol,
                'action' => 'BUY',
                'position_type' => $positionType,
                'qty' => $quantity,
                'price' => $price,
                'amount' => $amount,
                'is_real_trade' => true,
                'binance_order_id' => $order['orderId'],
                'notes' => "REAL TRADE - {$positionType} - Leverage: 5x - Amount: \${$amount}",
            ]);

            // Update portfolio
            $portfolio->calculateRealEquity();

            Log::info("✅ REAL POSITION SAVED - User: {$user->id}, Symbol: {$decision->symbol}, Order: {$order['orderId']}");

            return true;

        } catch (\Exception $e) {
            Log::error("Save real position failed: " . $e->getMessage());
            throw new \Exception("Position saving failed: " . $e->getMessage());
        }
    }

    /**
     * Calculate stop loss price
     */
    private function calculateStopLoss($entryPrice, $positionType)
    {
        $slPercent = 0.03; // 3% stop loss
        return $positionType === 'LONG' 
            ? $entryPrice * (1 - $slPercent)
            : $entryPrice * (1 + $slPercent);
    }

    /**
     * Calculate take profit price
     */
    private function calculateTakeProfit($entryPrice, $positionType)
    {
        $tpPercent = 0.06; // 6% take profit
        return $positionType === 'LONG' 
            ? $entryPrice * (1 + $tpPercent)
            : $entryPrice * (1 - $tpPercent);
    }

    /**
     * Convert AI action to position type
     */
    private function getPositionTypeFromAction($action)
    {
        return $action === 'BUY' ? 'LONG' : 'SHORT';
    }

    /**
     * Notifications (bisa integrate dengan existing notification system)
     */
    private function notifyRealTradeExecution($userId, $symbol, $action, $message)
    {
        Log::info("📢 REAL TRADE NOTIFICATION - User: {$userId}, {$message}");
        // TODO: Integrate dengan notification system yang ada
    }

    private function notifyRealTradeError($userId, $symbol, $action, $error)
    {
        Log::error("❌ REAL TRADE ERROR - User: {$userId}, {$error}");
        // TODO: Integrate dengan notification system yang ada
    }
}