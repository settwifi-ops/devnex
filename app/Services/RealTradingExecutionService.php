<?php
namespace App\Services;

use App\Models\User;
use App\Models\AiDecision;
use App\Models\UserPosition;
use App\Models\TradeHistory;
use App\Models\PendingOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RealTradingExecutionService
{
    private $binanceAccountService;

    // Konfigurasi Risk Management
    private $stopLossPercentage = 2.0; // 2% stop loss
    private $takeProfitPercentage = 4.0; // 4% take profit
    private $riskPerTrade = 0.02; // 2% risk per trade
    private $orderExpiryMinutes = 15; // 15 menit expiry
    private $leverage = 5; // 5x leverage

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
     * ✅ Execute real trade untuk single user dengan STOP LOSS & TAKE PROFIT
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
                
                return $this->executeRealTradeWithSLTP($user, $portfolio, $decision, $positionType);
            });

        } catch (\Exception $e) {
            Log::error("Real trade execution failed for user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ ENHANCED: Execute real trade dengan STOP LOSS & TAKE PROFIT
     */
    private function executeRealTradeWithSLTP(User $user, $portfolio, AiDecision $decision, $positionType)
    {
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($user->id);
            
            // Get balance
            $balance = $this->binanceAccountService->getFuturesBalanceOnly($user->id);
            $availableBalance = $balance['available'];
            
            // Calculate position size berdasarkan risk management
            $riskAmount = $availableBalance * $this->riskPerTrade;
            $riskAmount = max(11, min($riskAmount, 50));
            
            if ($riskAmount > $availableBalance) {
                throw new \Exception("Insufficient balance. Required: \${$riskAmount}, Available: \${$availableBalance}");
            }

            // Entry price dari decision
            $limitPrice = $decision->price;

            // Calculate quantity
            $quantity = $riskAmount / $limitPrice;
            $quantity = $this->calculatePreciseQuantity($binance, $decision->symbol, $quantity);

            if ($quantity <= 0) {
                throw new \Exception("Invalid quantity: {$quantity}");
            }

            // Set leverage
            $this->setLeverage($binance, $decision->symbol, $this->leverage);

            // Hitung Stop Loss & Take Profit
            $stopLossPrice = $this->calculateStopLossPrice($limitPrice, $positionType);
            $takeProfitPrice = $this->calculateTakeProfitPrice($limitPrice, $positionType);

            Log::info("🎯 PLACING ENHANCED REAL ORDER", [
                'user_id' => $user->id,
                'symbol' => $decision->symbol,
                'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
                'quantity' => $quantity,
                'limit_price' => $limitPrice,
                'stop_loss' => $stopLossPrice,
                'take_profit' => $takeProfitPrice,
                'amount' => $riskAmount,
                'risk_reward' => '1:2',
                'expires' => "{$this->orderExpiryMinutes} minutes"
            ]);

            // ==============================================
            // 1. PLACE MAIN LIMIT ORDER
            // ==============================================
            $order = $binance->futuresOrder(
                $positionType === 'LONG' ? 'BUY' : 'SELL',
                $decision->symbol,
                $quantity,
                $limitPrice,
                'LIMIT',
                [
                    'timeInForce' => 'GTC',
                    'leverage' => $this->leverage
                ]
            );

            if (!isset($order['orderId'])) {
                throw new \Exception("Limit order placement failed: " . json_encode($order));
            }

            $mainOrderId = $order['orderId'];
            
            // ==============================================
            // 2. PLACE STOP LOSS ORDER (STOP_MARKET)
            // ==============================================
            $stopLossOrderId = null;
            try {
                $stopLossSide = $positionType === 'LONG' ? 'SELL' : 'BUY';
                $stopLossOrder = $binance->futuresOrder(
                    $stopLossSide,
                    $decision->symbol,
                    $quantity,
                    0, // Price 0 untuk MARKET
                    'STOP_MARKET',
                    [
                        'stopPrice' => $stopLossPrice,
                        'closePosition' => 'true',
                        'reduceOnly' => 'true'
                    ]
                );
                
                $stopLossOrderId = $stopLossOrder['orderId'] ?? null;
                Log::info("🛑 STOP LOSS ORDER PLACED", ['order_id' => $stopLossOrderId]);
            } catch (\Exception $e) {
                Log::warning("⚠️ Stop loss order failed (non-critical): " . $e->getMessage());
            }

            // ==============================================
            // 3. PLACE TAKE PROFIT ORDER (LIMIT)
            // ==============================================
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
                Log::info("🎯 TAKE PROFIT ORDER PLACED", ['order_id' => $takeProfitOrderId]);
            } catch (\Exception $e) {
                Log::warning("⚠️ Take profit order failed (non-critical): " . $e->getMessage());
            }

            // ==============================================
            // 4. SAVE PENDING ORDER DENGAN SL/TP INFO
            // ==============================================
            PendingOrder::create([
                'user_id' => $user->id,
                'ai_decision_id' => $decision->id,
                'symbol' => $decision->symbol,
                'binance_order_id' => $mainOrderId,
                'stop_loss_order_id' => $stopLossOrderId,
                'take_profit_order_id' => $takeProfitOrderId,
                'limit_price' => $limitPrice,
                'stop_loss_price' => $stopLossPrice,
                'take_profit_price' => $takeProfitPrice,
                'quantity' => $quantity,
                'side' => $positionType === 'LONG' ? 'BUY' : 'SELL',
                'position_type' => $positionType,
                'expires_at' => now()->addMinutes($this->orderExpiryMinutes),
                'status' => 'PENDING',
                'order_status' => 'NEW',
                'notes' => "Limit order with SL: \${$stopLossPrice}, TP: \${$takeProfitPrice}"
            ]);

            Log::info("✅ ENHANCED ORDER PLACED", [
                'user_id' => $user->id,
                'main_order' => $mainOrderId,
                'stop_loss' => $stopLossOrderId ?? 'None',
                'take_profit' => $takeProfitOrderId ?? 'None',
                'expires_at' => now()->addMinutes($this->orderExpiryMinutes)->format('H:i:s')
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("ENHANCED REAL TRADE execution failed for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ Calculate Stop Loss Price
     */
    private function calculateStopLossPrice($entryPrice, $positionType)
    {
        if ($positionType === 'LONG') {
            // Untuk LONG: stop loss di bawah entry
            return $entryPrice * (1 - ($this->stopLossPercentage / 100));
        } else {
            // Untuk SHORT: stop loss di atas entry  
            return $entryPrice * (1 + ($this->stopLossPercentage / 100));
        }
    }

    /**
     * ✅ Calculate Take Profit Price
     */
    private function calculateTakeProfitPrice($entryPrice, $positionType)
    {
        if ($positionType === 'LONG') {
            // Untuk LONG: take profit di atas entry
            return $entryPrice * (1 + ($this->takeProfitPercentage / 100));
        } else {
            // Untuk SHORT: take profit di bawah entry
            return $entryPrice * (1 - ($this->takeProfitPercentage / 100));
        }
    }

    /**
     * ✅ ENHANCED: Check pending orders yang expired & cancel SL/TP juga
     */
    public function checkPendingOrders()
    {
        try {
            $expiredOrders = PendingOrder::where('status', 'PENDING')
                ->where('expires_at', '<=', now())
                ->get();

            Log::info("🕒 Checking expired orders: " . $expiredOrders->count());

            $cancelledCount = 0;
            foreach ($expiredOrders as $order) {
                if ($this->cancelExpiredOrderWithSLTP($order)) {
                    $cancelledCount++;
                }
            }

            Log::info("✅ Cancelled {$cancelledCount} expired orders with their SL/TP");
            return $cancelledCount;
            
        } catch (\Exception $e) {
            Log::error("❌ Check pending orders failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ ENHANCED: Cancel expired order beserta SL/TP orders
     */
    private function cancelExpiredOrderWithSLTP(PendingOrder $pendingOrder)
    {
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($pendingOrder->user_id);
            
            $cancelledOrders = [];
            
            // 1. Cancel main order
            try {
                $result = $binance->futuresCancel($pendingOrder->symbol, $pendingOrder->binance_order_id);
                $cancelledOrders[] = 'Main order';
                Log::info("🗑️ Main order cancelled: " . $pendingOrder->binance_order_id);
            } catch (\Exception $e) {
                Log::warning("Main order cancel failed: " . $e->getMessage());
            }

            // 2. Cancel stop loss order jika ada
            if ($pendingOrder->stop_loss_order_id) {
                try {
                    $binance->futuresCancel($pendingOrder->symbol, $pendingOrder->stop_loss_order_id);
                    $cancelledOrders[] = 'Stop loss';
                    Log::info("🗑️ Stop loss order cancelled: " . $pendingOrder->stop_loss_order_id);
                } catch (\Exception $e) {
                    Log::warning("Stop loss cancel failed: " . $e->getMessage());
                }
            }

            // 3. Cancel take profit order jika ada
            if ($pendingOrder->take_profit_order_id) {
                try {
                    $binance->futuresCancel($pendingOrder->symbol, $pendingOrder->take_profit_order_id);
                    $cancelledOrders[] = 'Take profit';
                    Log::info("🗑️ Take profit order cancelled: " . $pendingOrder->take_profit_order_id);
                } catch (\Exception $e) {
                    Log::warning("Take profit cancel failed: " . $e->getMessage());
                }
            }

            // Update status di database
            $pendingOrder->update([
                'status' => 'EXPIRED',
                'notes' => 'Automatically cancelled after ' . $this->orderExpiryMinutes . ' minutes. ' .
                          'Cancelled: ' . implode(', ', $cancelledOrders)
            ]);

            Log::info("🕒 ORDER EXPIRED WITH SL/TP: " . $pendingOrder->symbol, [
                'user_id' => $pendingOrder->user_id,
                'main_order' => $pendingOrder->binance_order_id,
                'cancelled_orders' => $cancelledOrders
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("❌ Cancel expired order with SL/TP failed: " . $e->getMessage());
            
            // Tetap mark as expired meski cancel gagal
            $pendingOrder->update([
                'status' => 'EXPIRED',
                'notes' => 'Auto expiry (cancel failed: ' . $e->getMessage() . ')'
            ]);
            
            return false;
        }
    }

    /**
     * ✅ METHOD BARU: Add stop loss to filled orders that don't have one
     */
    public function addStopLossToFilledOrders($userId = null)
    {
        try {
            $query = PendingOrder::where('status', 'FILLED')
                ->whereNull('stop_loss_order_id');
            
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $orders = $query->get();
            
            Log::info("🔧 Adding stop loss to {$orders->count()} filled orders");
            
            $addedCount = 0;
            
            foreach ($orders as $order) {
                try {
                    if ($this->addStopLossToFilledOrder($order)) {
                        $addedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to add stop loss to order {$order->id}: " . $e->getMessage());
                }
            }
            
            Log::info("✅ Added stop loss to {$addedCount} filled orders");
            return $addedCount;
            
        } catch (\Exception $e) {
            Log::error("❌ Add stop loss to filled orders failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ METHOD BARU: Add stop loss to single filled order
     */
    private function addStopLossToFilledOrder(PendingOrder $order)
    {
        try {
            $binance = $this->binanceAccountService->getBinanceInstance($order->user_id);
            
            // Hitung stop loss price
            $stopLossPrice = $this->calculateStopLossPrice(
                $order->limit_price ?? $order->executed_price, 
                $order->position_type
            );
            
            $stopLossSide = $order->side === 'BUY' ? 'SELL' : 'BUY';
            
            // Place stop loss order
            $stopLossOrder = $binance->futuresOrder(
                $stopLossSide,
                $order->symbol,
                $order->quantity,
                0, // Price 0 untuk MARKET
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
            
            // Update pending order dengan stop loss info
            $order->update([
                'stop_loss_order_id' => $stopLossOrder['orderId'],
                'stop_loss_price' => $stopLossPrice,
                'notes' => $order->notes . " | Stop loss added post-fill: " . $stopLossOrder['orderId']
            ]);
            
            Log::info("✅ STOP LOSS ADDED TO FILLED ORDER", [
                'order_id' => $order->id,
                'stop_loss_id' => $stopLossOrder['orderId'],
                'stop_loss_price' => $stopLossPrice
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("❌ Failed to add stop loss to filled order {$order->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ METHOD BARU: Get active stop loss orders for monitoring
     */
    public function getActiveStopLossOrders($userId = null)
    {
        try {
            $query = PendingOrder::whereNotNull('stop_loss_order_id')
                ->where('status', 'FILLED');
            
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $orders = $query->get();
            
            $activeSL = [];
            foreach ($orders as $order) {
                try {
                    $binance = $this->binanceAccountService->getBinanceInstance($order->user_id);
                    
                    // Cek status stop loss order di Binance
                    $slStatus = $binance->futuresGetOrder([
                        'symbol' => $order->symbol,
                        'orderId' => $order->stop_loss_order_id
                    ]);
                    
                    $activeSL[] = [
                        'order_id' => $order->id,
                        'symbol' => $order->symbol,
                        'stop_loss_id' => $order->stop_loss_order_id,
                        'stop_loss_price' => $order->stop_loss_price,
                        'status' => $slStatus['status'] ?? 'UNKNOWN',
                        'triggered' => ($slStatus['status'] ?? '') === 'FILLED'
                    ];
                    
                } catch (\Exception $e) {
                    Log::warning("Failed to check stop loss status: " . $e->getMessage());
                }
            }
            
            return $activeSL;
            
        } catch (\Exception $e) {
            Log::error("❌ Get active stop loss orders failed: " . $e->getMessage());
            return [];
        }
    }

    // ==============================================
    // HELPER METHODS (tetap sama seperti sebelumnya)
    // ==============================================
    
    private function getCurrentPrice($binance, $symbol)
    {
        try {
            return $binance->price($symbol);
        } catch (\Exception $e) {
            Log::error("Price fetch failed for {$symbol}: " . $e->getMessage());
            return null;
        }
    }

    private function calculatePreciseQuantity($binance, $symbol, $quantity)
    {
        try {
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
                
                $precision = strlen(substr(strrchr($stepSize, '.'), 1)) - 1;
                $adjustedQty = floor($quantity / $stepSize) * $stepSize;
                $adjustedQty = round($adjustedQty, $precision);
                
                if ($adjustedQty < $minQty) {
                    throw new \Exception("Quantity too small. Min: {$minQty}, Calculated: {$adjustedQty}");
                }
                if ($adjustedQty > $maxQty) {
                    throw new \Exception("Quantity too large. Max: {$maxQty}, Calculated: {$adjustedQty}");
                }
                
                return $adjustedQty;
            }

            return round($quantity, 6);
        } catch (\Exception $e) {
            Log::warning("Quantity precision adjustment failed: " . $e->getMessage());
            return round($quantity, 6);
        }
    }

    private function setLeverage($binance, $symbol, $leverage)
    {
        try {
            if (method_exists($binance, 'futures_change_leverage')) {
                $result = $binance->futures_change_leverage($symbol, $leverage);
            } elseif (method_exists($binance, 'change_leverage')) {
                $result = $binance->change_leverage($symbol, $leverage);
            } else {
                Log::info("⚙️ Leverage setting skipped - using default");
                return null;
            }
            
            Log::info("🎯 Leverage set to {$leverage}x for {$symbol}", (array)$result);
            return $result;
            
        } catch (\Exception $e) {
            Log::warning("⚠️ Leverage setting failed (continuing anyway): " . $e->getMessage());
            return null;
        }
    }

    private function getPositionTypeFromAction($action)
    {
        return $action === 'BUY' ? 'LONG' : 'SHORT';
    }

    private function notifyRealTradeExecution($userId, $symbol, $action, $message)
    {
        Log::info("📢 REAL TRADE NOTIFICATION - User: {$userId}, {$message}");
    }

    private function notifyRealTradeError($userId, $symbol, $action, $error)
    {
        Log::error("❌ REAL TRADE ERROR - User: {$userId}, {$error}");
    }
}