<?php
// app/Http/Livewire/RealTradingPage.php
namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\PendingOrder;
use App\Services\BinanceAccountService;
use App\Services\RealTradingExecutionService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RealTradingPage extends Component
{
    public $user;
    public $hasRealSubscription = false;
    public $binanceConnected = false;
    public $realTradingEnabled = false;
    public $realBalance = 0;
    public $futuresBalance = 0;
    public $isTestnet = true;
    public $minBalanceRequired = 10;
    
    // Form fields
    public $connectionType = 'testnet';
    public $api_key = '';
    public $api_secret = '';
    
    // Loading states
    public $upgrading = false;
    public $connecting = false;
    public $refreshing = false;
    public $toggling = false;
    
    // Account management
    public $showAccountManagement = false;
    public $deleting = false;
    public $userAccounts = [];

    // Pending orders management
    public $pendingOrders = [];
    public $pendingOrdersCount = 0;
    public $refreshingOrders = false;
    public $cancellingOrderId = null;

    // Trading positions langsung dari Binance
    public $binancePositions = [];
    public $activePositionsCount = 0;
    public $totalUnrealizedPnl = 0;
    public $loadingPositions = false;
    public $closingPositionId = null;

    // Confirmation modals
    public $showCancelConfirm = false;
    public $orderToCancel = null;
    public $showCloseConfirm = false;
    public $positionToClose = null;

    // Tabs
    public $activeTab = 'active';

    // Real trading execution service
    protected $realTradingService;

    public function boot()
    {
        $this->realTradingService = app(RealTradingExecutionService::class);
    }

    public function mount()
    {
        $this->user = Auth::user();
        $this->forceLoadUserData();
        $this->loadUserAccounts();
        $this->loadPendingOrders();
        $this->loadBinancePositions();
        
        Log::info("🔍 MOUNT - Initial State", [
            'user_id' => $this->user->id,
            'has_subscription' => $this->hasRealSubscription,
            'binance_connected' => $this->binanceConnected,
            'trading_enabled' => $this->realTradingEnabled,
            'pending_orders' => $this->pendingOrdersCount,
            'binance_positions' => count($this->binancePositions)
        ]);
    }

    /**
     * Force load data dengan refresh lengkap
     */
    private function forceLoadUserData()
    {
        try {
            $this->user->refresh();
            
            if ($this->user->portfolio) {
                $this->user->portfolio->refresh();
            }

            $this->hasRealSubscription = (bool) $this->user->real_trading_subscribed;
            
            $portfolio = $this->user->portfolio;
            
            if ($portfolio) {
                $this->binanceConnected = (bool) $portfolio->real_trading_active;
                $this->realTradingEnabled = (bool) $portfolio->real_trading_enabled;
                $this->realBalance = $portfolio->real_balance ?? 0;
                $this->futuresBalance = $portfolio->real_balance ?? 0;
                $this->isTestnet = ($portfolio->binance_environment ?? 'testnet') === 'testnet';

                Log::info("✅ FORCE LOAD - Data Loaded", [
                    'user_id' => $this->user->id,
                    'has_subscription' => $this->hasRealSubscription,
                    'binance_connected' => $this->binanceConnected,
                    'trading_enabled' => $this->realTradingEnabled,
                    'real_balance' => $this->realBalance,
                    'is_testnet' => $this->isTestnet
                ]);
            } else {
                $this->resetToDefaultState();
                Log::warning("❌ FORCE LOAD - No Portfolio", ['user_id' => $this->user->id]);
            }

        } catch (\Exception $e) {
            Log::error("❌ FORCE LOAD - Error", [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);
            $this->resetToDefaultState();
        }
    }

    private function resetToDefaultState()
    {
        $this->binanceConnected = false;
        $this->realTradingEnabled = false;
        $this->realBalance = 0;
        $this->futuresBalance = 0;
        $this->isTestnet = true;
    }

    /**
     * Load user's connected accounts
     */
    private function loadUserAccounts()
    {
        try {
            $binanceService = app(BinanceAccountService::class);
            $this->userAccounts = $binanceService->getUserAccounts($this->user->id);
        } catch (\Exception $e) {
            $this->userAccounts = [];
            Log::warning("Failed to load user accounts: " . $e->getMessage());
        }
    }

    /**
     * Load pending orders untuk user
     */
    public function loadPendingOrders()
    {
        try {
            $this->pendingOrders = PendingOrder::where('user_id', $this->user->id)
                ->where(function($query) {
                    $query->whereIn('status', ['PENDING', 'PARTIALLY_FILLED', 'NEW'])
                          ->orWhere(function($q) {
                              $q->where('status', 'FILLED')
                                ->where(function($sq) {
                                    $sq->whereNull('order_status')
                                       ->orWhereNotIn('order_status', ['FILLED', 'CANCELLED']);
                                });
                          });
                })
                ->where(function($query) {
                    $query->whereNull('order_status')
                          ->orWhereNotIn('order_status', ['FILLED', 'CANCELLED']);
                })
                ->with('aiDecision')
                ->orderBy('created_at', 'desc')
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
                        'order_status' => $order->order_status,
                        'executed_qty' => $order->executed_qty,
                        'avg_price' => $order->avg_price,
                        'binance_order_id' => $order->binance_order_id,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'expires_at' => $order->expires_at ? $order->expires_at->format('Y-m-d H:i:s') : null,
                        'notes' => $order->notes,
                        'ai_decision' => $order->aiDecision ? [
                            'id' => $order->aiDecision->id,
                            'price' => $order->aiDecision->price,
                            'action' => $order->aiDecision->action
                        ] : null
                    ];
                })
                ->toArray();
                
            $this->pendingOrdersCount = count($this->pendingOrders);
            
            Log::info("📦 PENDING ORDERS LOADED", [
                'user_id' => $this->user->id,
                'count' => $this->pendingOrdersCount
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Failed to load pending orders: " . $e->getMessage());
            $this->pendingOrders = [];
            $this->pendingOrdersCount = 0;
        }
    }

    /**
     * Load trading positions langsung dari Binance API
     */
    public function loadBinancePositions()
    {
        $this->loadingPositions = true;
        
        try {
            if (!$this->binanceConnected) {
                $this->binancePositions = [];
                $this->activePositionsCount = 0;
                $this->totalUnrealizedPnl = 0;
                return;
            }
            
            $binanceService = app(BinanceAccountService::class);
            $binance = $binanceService->getBinanceInstance($this->user->id);
            
            // 1. Get all open positions from Binance
            $positions = $binance->futuresPositionRisk();
            
            // 2. Filter hanya yang ada positionAmount (ada posisi terbuka)
            $activePositions = array_filter($positions, function($position) {
                return (float) $position['positionAmt'] != 0;
            });
            
            // 3. Format data
            $formattedPositions = [];
            $totalUnrealizedPnl = 0;
            
            foreach ($activePositions as $position) {
                $positionAmt = (float) $position['positionAmt'];
                $entryPrice = (float) $position['entryPrice'];
                $markPrice = (float) $position['markPrice'];
                $unrealizedProfit = (float) $position['unRealizedProfit'];
                
                // Determine side (positive amount = LONG, negative = SHORT)
                $side = $positionAmt > 0 ? 'BUY' : 'SELL';
                $positionType = $positionAmt > 0 ? 'LONG' : 'SHORT';
                $quantity = abs($positionAmt);
                
                // Calculate P&L percentage
                $pnlPercentage = $entryPrice > 0 ? ($unrealizedProfit / ($entryPrice * $quantity)) * 100 : 0;
                
                $formattedPosition = [
                    'symbol' => $position['symbol'],
                    'side' => $side,
                    'position_type' => $positionType,
                    'entry_price' => $entryPrice,
                    'mark_price' => $markPrice,
                    'quantity' => $quantity,
                    'unrealized_pnl' => $unrealizedProfit,
                    'pnl_percentage' => $pnlPercentage,
                    'leverage' => (int) $position['leverage'],
                    'liquidation_price' => (float) $position['liquidationPrice'],
                    'margin_type' => $position['marginType'],
                    'isolated_margin' => (float) $position['isolatedMargin'],
                    'position_side' => $position['positionSide'],
                    'index' => count($formattedPositions) // Untuk reference di modal
                ];
                
                $formattedPositions[] = $formattedPosition;
                $totalUnrealizedPnl += $unrealizedProfit;
            }
            
            // 4. Sort by unrealized P&L (biggest losers first, then winners)
            usort($formattedPositions, function($a, $b) {
                return $a['unrealized_pnl'] <=> $b['unrealized_pnl'];
            });
            
            $this->binancePositions = $formattedPositions;
            $this->activePositionsCount = count($formattedPositions);
            $this->totalUnrealizedPnl = $totalUnrealizedPnl;
            
            Log::info("📊 BINANCE POSITIONS LOADED", [
                'user_id' => $this->user->id,
                'count' => $this->activePositionsCount,
                'total_unrealized_pnl' => $totalUnrealizedPnl
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Failed to load Binance positions: " . $e->getMessage());
            $this->binancePositions = [];
            $this->activePositionsCount = 0;
            $this->totalUnrealizedPnl = 0;
            session()->flash('error', 'Failed to load positions from Binance: ' . $e->getMessage());
        }
        
        $this->loadingPositions = false;
    }

    /**
     * Refresh pending orders and auto-clean filled ones
     */
    public function refreshPendingOrders()
    {
        $this->refreshingOrders = true;
        
        try {
            if (!$this->binanceConnected) {
                session()->flash('error', 'Binance not connected');
                $this->refreshingOrders = false;
                return;
            }
            
            $binanceService = app(BinanceAccountService::class);
            $binance = $binanceService->getBinanceInstance($this->user->id);
            
            // Ambil semua pending orders
            $pendingOrders = PendingOrder::where('user_id', $this->user->id)
                ->whereIn('status', ['PENDING', 'PARTIALLY_FILLED', 'NEW', 'FILLED'])
                ->get();
            
            $filledCount = 0;
            $cancelledCount = 0;
            
            foreach ($pendingOrders as $order) {
                try {
                    if (!$order->binance_order_id) continue;
                    
                    // Cek status di Binance
                    $binanceStatus = $binance->futuresGetOrder([
                        'symbol' => $order->symbol,
                        'orderId' => $order->binance_order_id
                    ]);
                    
                    $orderStatus = $binanceStatus['status'] ?? 'UNKNOWN';
                    $executedQty = $binanceStatus['executedQty'] ?? 0;
                    $avgPrice = $binanceStatus['avgPrice'] ?? 0;
                    
                    // Update status lokal
                    $order->update([
                        'order_status' => $orderStatus,
                        'executed_qty' => $executedQty,
                        'avg_price' => $avgPrice,
                        'last_checked' => now()
                    ]);
                    
                    // Jika sudah FILLED di Binance
                    if ($orderStatus === 'FILLED' && $order->status !== 'FILLED') {
                        $order->update(['status' => 'FILLED']);
                        $filledCount++;
                    }
                    
                    // Jika CANCELLED di Binance
                    if ($orderStatus === 'CANCELLED' && $order->status !== 'CANCELLED') {
                        $order->update(['status' => 'CANCELLED']);
                        $cancelledCount++;
                    }
                    
                } catch (\Exception $e) {
                    Log::warning("Failed to check Binance status for order {$order->id}: " . $e->getMessage());
                }
            }
            
            // Check expired orders via service
            $expiredCount = 0;
            if ($this->realTradingService) {
                $expiredCount = $this->realTradingService->checkPendingOrders();
            }
            
            // Reload data
            $this->loadPendingOrders();
            $this->loadBinancePositions();
            
            // Tampilkan message
            $message = 'Orders refreshed!';
            if ($filledCount > 0) $message .= " {$filledCount} order(s) filled.";
            if ($cancelledCount > 0) $message .= " {$cancelledCount} order(s) cancelled.";
            if ($expiredCount > 0) $message .= " {$expiredCount} order(s) expired.";
            
            session()->flash('message', $message);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Refresh failed: ' . $e->getMessage());
        }
        
        $this->refreshingOrders = false;
    }

    /**
     * Close position langsung di Binance
     */
    public function closePosition($positionData)
    {
        $this->closingPositionId = $positionData['symbol'];
        
        try {
            if (!$this->binanceConnected) {
                throw new \Exception('Binance not connected');
            }
            
            $binanceService = app(BinanceAccountService::class);
            $binance = $binanceService->getBinanceInstance($this->user->id);
            
            $symbol = $positionData['symbol'];
            $quantity = $positionData['quantity'];
            $side = $positionData['side'];
            
            // Determine order side (opposite of position)
            $orderSide = $side === 'BUY' ? 'SELL' : 'BUY';
            
            // Get current price untuk market order
            $ticker = $binance->futuresPrices();
            $currentPrice = null;
            
            foreach ($ticker as $item) {
                if ($item['symbol'] === $symbol) {
                    $currentPrice = (float) $item['price'];
                    break;
                }
            }
            
            if (!$currentPrice) {
                throw new \Exception("Failed to get current price for {$symbol}");
            }
            
            // Place market order to close position
            $order = $binance->futuresNewOrder([
                'symbol' => $symbol,
                'side' => $orderSide,
                'type' => 'MARKET',
                'quantity' => $quantity,
                'reduceOnly' => true,
            ]);
            
            Log::info("📤 CLOSE POSITION ORDER", [
                'symbol' => $symbol,
                'side' => $orderSide,
                'quantity' => $quantity,
                'order_id' => $order['orderId'] ?? 'N/A',
                'price' => $currentPrice
            ]);
            
            // Tunggu sebentar lalu reload positions
            sleep(2);
            $this->loadBinancePositions();
            $this->loadPendingOrders();
            
            session()->flash('message', 
                "Position {$symbol} closed successfully! " .
                "Market order placed at $" . number_format($currentPrice, 4)
            );
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to close position: ' . $e->getMessage());
            Log::error("❌ Close Position Error: " . $e->getMessage());
        } finally {
            $this->closingPositionId = null;
        }
    }

    /**
     * Confirm position close
     */
    public function confirmClosePosition($positionIndex)
    {
        if (!isset($this->binancePositions[$positionIndex])) {
            session()->flash('error', 'Position not found');
            return;
        }
        
        $this->positionToClose = $this->binancePositions[$positionIndex];
        $this->showCloseConfirm = true;
    }

    /**
     * Close position confirmed
     */
    public function closePositionConfirmed()
    {
        if (!$this->positionToClose) {
            session()->flash('error', 'No position selected');
            $this->showCloseConfirm = false;
            return;
        }
        
        $this->closePosition($this->positionToClose);
        $this->positionToClose = null;
        $this->showCloseConfirm = false;
    }

    /**
     * Close confirmation modal
     */
    public function closeCloseConfirm()
    {
        $this->showCloseConfirm = false;
        $this->positionToClose = null;
    }

    /**
     * Check order status on Binance
     */
    public function checkOrderStatus($orderId)
    {
        try {
            $order = PendingOrder::find($orderId);
            
            if (!$order || $order->user_id != $this->user->id) {
                session()->flash('error', 'Order not found');
                return;
            }
            
            $binanceService = app(BinanceAccountService::class);
            $binance = $binanceService->getBinanceInstance($this->user->id);
            
            // Get order status from Binance
            $orderStatus = $binance->futuresGetOrder([
                'symbol' => $order->symbol,
                'orderId' => $order->binance_order_id
            ]);
            
            // Update local status
            $order->update([
                'order_status' => $orderStatus['status'] ?? 'UNKNOWN',
                'executed_qty' => $orderStatus['executedQty'] ?? 0,
                'avg_price' => $orderStatus['avgPrice'] ?? 0,
                'last_checked' => now()
            ]);
            
            // If order is FILLED, update status
            if (($orderStatus['status'] ?? '') === 'FILLED') {
                $order->update(['status' => 'FILLED']);
            }
            
            $this->loadPendingOrders();
            $this->loadBinancePositions();
            
            session()->flash('info', 
                "Order Status: {$orderStatus['status']} | " .
                "Filled: {$orderStatus['executedQty']}/{$orderStatus['origQty']}"
            );
            
        } catch (\Exception $e) {
            session()->flash('error', 'Status check failed: ' . $e->getMessage());
        }
    }

    /**
     * Confirm order cancellation
     */
    public function confirmCancelOrder($orderId)
    {
        $order = PendingOrder::where('id', $orderId)
            ->where('user_id', $this->user->id)
            ->whereIn('status', ['PENDING', 'PARTIALLY_FILLED'])
            ->first();
        
        if ($order) {
            $this->orderToCancel = $order;
            $this->showCancelConfirm = true;
        } else {
            session()->flash('error', 'Order not found or already processed');
        }
    }

    /**
     * Cancel pending order
     */
    public function cancelPendingOrder()
    {
        if (!$this->orderToCancel) {
            session()->flash('error', 'No order selected');
            $this->showCancelConfirm = false;
            return;
        }
        
        $this->cancellingOrderId = $this->orderToCancel->id;
        
        try {
            $binanceService = app(BinanceAccountService::class);
            
            // Get Binance instance
            $binance = $binanceService->getBinanceInstance($this->user->id);
            
            // Cancel order di Binance
            $result = $binance->futuresCancel(
                $this->orderToCancel->symbol, 
                $this->orderToCancel->binance_order_id
            );
            
            Log::info("📤 Binance Cancel Response", ['result' => $result]);
            
            // Update status di database
            $this->orderToCancel->update([
                'status' => 'CANCELLED',
                'notes' => 'Cancelled manually by user at ' . now()->format('Y-m-d H:i:s'),
                'cancelled_at' => now()
            ]);
            
            // Reload orders
            $this->loadPendingOrders();
            
            session()->flash('message', 
                "Order {$this->orderToCancel->symbol} cancelled successfully! " .
                "Order ID: {$this->orderToCancel->binance_order_id}"
            );
            
            Log::info("🗑️ ORDER CANCELLED SUCCESSFULLY", [
                'user_id' => $this->user->id,
                'order_id' => $this->orderToCancel->id,
                'binance_order_id' => $this->orderToCancel->binance_order_id,
                'symbol' => $this->orderToCancel->symbol
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Manual order cancellation failed", [
                'user_id' => $this->user->id,
                'order_id' => $this->orderToCancel->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Jika cancel gagal di Binance, mark as expired locally
            $this->orderToCancel->update([
                'status' => 'EXPIRED',
                'notes' => 'Cancel failed: ' . $e->getMessage()
            ]);
            
            $this->loadPendingOrders();
            
            session()->flash('warning', 
                "Order marked as expired (Binance cancel failed). " .
                "Error: " . $e->getMessage()
            );
        } finally {
            $this->cancellingOrderId = null;
            $this->orderToCancel = null;
            $this->showCancelConfirm = false;
        }
    }

    /**
     * Close cancel confirmation modal
     */
    public function closeCancelConfirm()
    {
        $this->showCancelConfirm = false;
        $this->orderToCancel = null;
    }

    /**
     * Get order summary
     */
    public function getOrderSummary($order)
    {
        $totalValue = $order['limit_price'] * $order['quantity'];
        
        if ($order['expires_at']) {
            $expiresAt = Carbon::parse($order['expires_at']);
            $timeLeft = now()->diffInMinutes($expiresAt, false);
            $isExpired = $timeLeft <= 0;
            $timeLeftText = $isExpired ? 'Expired' : ($timeLeft . ' minutes');
        } else {
            $timeLeft = null;
            $isExpired = false;
            $timeLeftText = 'No expiry';
        }
        
        return [
            'total_value' => number_format($totalValue, 2),
            'time_left' => $timeLeftText,
            'is_expired' => $isExpired,
            'badge_color' => $this->getStatusBadgeColor($order['status']),
            'order_status_color' => $this->getOrderStatusBadgeColor($order['order_status'] ?? '')
        ];
    }

    /**
     * Get badge color for order status
     */
    private function getStatusBadgeColor($status)
    {
        switch (strtoupper($status)) {
            case 'PENDING':
                return 'warning';
            case 'FILLED':
                return 'success';
            case 'CANCELLED':
                return 'secondary';
            case 'EXPIRED':
                return 'danger';
            case 'PARTIALLY_FILLED':
                return 'info';
            default:
                return 'light';
        }
    }

    /**
     * Get badge color for Binance order status
     */
    private function getOrderStatusBadgeColor($orderStatus)
    {
        switch (strtoupper($orderStatus)) {
            case 'NEW':
                return 'blue';
            case 'PARTIALLY_FILLED':
                return 'yellow';
            case 'FILLED':
                return 'green';
            case 'CANCELLED':
                return 'red';
            case 'REJECTED':
                return 'red';
            case 'EXPIRED':
                return 'gray';
            default:
                return 'gray';
        }
    }

    /**
     * Get P&L badge color
     */
    public function getPnlBadgeColor($pnl)
    {
        if ($pnl > 0) return 'success';
        if ($pnl < 0) return 'danger';
        return 'secondary';
    }

    /**
     * Format P&L with sign and color
     */
    public function formatPnl($pnl)
    {
        $sign = $pnl >= 0 ? '+' : '';
        $color = $pnl >= 0 ? 'text-green-600' : 'text-red-600';
        return [
            'formatted' => $sign . '$' . number_format(abs($pnl), 2),
            'color' => $color,
            'sign' => $sign
        ];
    }

    /**
     * Switch tab
     */
    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    /**
     * Refresh positions dari Binance
     */
    public function refreshPositions()
    {
        $this->loadBinancePositions();
        session()->flash('info', 'Positions refreshed from Binance!');
    }

    /**
     * Refresh semua data
     */
    public function refreshData()
    {
        $this->forceLoadUserData();
        $this->loadUserAccounts();
        $this->loadPendingOrders();
        $this->loadBinancePositions();
        session()->flash('message', 'All data refreshed successfully!');
    }

    public function upgradeToRealTrading()
    {
        $this->upgrading = true;
        
        try {
            $this->user->real_trading_subscribed = true;
            $this->user->save();
            
            $this->forceLoadUserData();
            session()->flash('message', 'Real trading subscription activated!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Upgrade failed: ' . $e->getMessage());
        }
        
        $this->upgrading = false;
    }

    public function connectBinance()
    {
        $this->validate([
            'api_key' => 'required|string',
            'api_secret' => 'required|string'
        ]);
        
        $this->connecting = true;
        
        try {
            $isTestnet = $this->connectionType === 'testnet';
            
            $binanceService = app(BinanceAccountService::class);
            $result = $binanceService->connectAccount(
                $this->user->id,
                $this->api_key,
                $this->api_secret,
                $isTestnet
            );
            
            Log::info("🔗 CONNECT BINANCE - Result", [
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'No message',
                'is_testnet' => $isTestnet
            ]);
            
            if ($result['success']) {
                $this->api_key = '';
                $this->api_secret = '';
                
                $this->forceLoadUserData();
                $this->loadUserAccounts();
                $this->loadPendingOrders();
                $this->loadBinancePositions();
                
                session()->flash('message', 
                    "Binance " . ($isTestnet ? 'Testnet' : 'Live') . " connected! " .
                    "Balance: $" . number_format($result['balance'] ?? 0, 2)
                );
                
                // Dispatch event for frontend
                $this->dispatch('binance-connected');
                
            } else {
                session()->flash('error', 'Connection failed: ' . $result['message']);
            }
            
        } catch (\Exception $e) {
            Log::error("❌ CONNECT BINANCE - Error", [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Connection error: ' . $e->getMessage());
        }
        
        $this->connecting = false;
    }

    public function toggleRealTrading()
    {
        if ($this->futuresBalance < $this->minBalanceRequired) {
            session()->flash('error', 
                "Minimum balance required: \${$this->minBalanceRequired}. " .
                "Current: \$" . number_format($this->futuresBalance, 2)
            );
            return;
        }

        $this->toggling = true;
        
        try {
            $this->user->portfolio->real_trading_enabled = !$this->realTradingEnabled;
            $this->user->portfolio->save();
            
            $this->forceLoadUserData();
            
            $status = $this->realTradingEnabled ? 'enabled' : 'disabled';
            session()->flash('message', "Real trading {$status}!");
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update trading status: ' . $e->getMessage());
        }
        
        $this->toggling = false;
    }

    public function refreshBalance()
    {
        $this->refreshing = true;
        
        try {
            $binanceService = app(BinanceAccountService::class);
            $result = $binanceService->updateBalanceSnapshot($this->user->id);
            
            if ($result) {
                $this->forceLoadUserData();
                session()->flash('message', 'Balance updated!');
            } else {
                session()->flash('error', 'Failed to update balance');
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
        
        $this->refreshing = false;
    }

    public function switchConnectionType($type)
    {
        $this->connectionType = $type;
        $this->reset(['api_key', 'api_secret']);
    }

    public function toggleAccountManagement()
    {
        $this->showAccountManagement = !$this->showAccountManagement;
        if ($this->showAccountManagement) {
            $this->loadUserAccounts();
        }
    }

    public function deleteAccount($accountId = null, $isTestnet = null)
    {
        $this->deleting = true;
        
        try {
            $binanceService = app(BinanceAccountService::class);
            
            if ($accountId) {
                $account = \App\Models\UserBinanceAccount::find($accountId);
                if ($account) {
                    $result = $binanceService->deleteAccount($this->user->id, $account->is_testnet);
                } else {
                    throw new \Exception('Account not found');
                }
            } else {
                $result = $binanceService->deleteAccount($this->user->id, $isTestnet);
            }
            
            if ($result['success']) {
                $this->forceLoadUserData();
                $this->loadUserAccounts();
                $this->loadPendingOrders();
                $this->loadBinancePositions();
                $this->showAccountManagement = false;
                
                session()->flash('message', 
                    $result['message'] . ' (' . $result['deleted_count'] . ' accounts)'
                );
            } else {
                session()->flash('error', $result['message']);
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
        
        $this->deleting = false;
    }

    public function switchToTestnet()
    {
        $this->connectionType = 'testnet';
        $this->reset(['api_key', 'api_secret']);
        $this->showAccountManagement = false;
        session()->flash('message', 'Switched to Testnet mode. Enter your Testnet API keys.');
    }

    public function switchToMainnet()
    {
        $this->connectionType = 'mainnet';
        $this->reset(['api_key', 'api_secret']);
        $this->showAccountManagement = false;
        session()->flash('message', 'Switched to Live Trading mode. Enter your Mainnet API keys.');
    }

    /**
     * Force redirect ke dashboard
     */
    public function forceRedirectToDashboard()
    {
        Log::info("🚀 FORCE REDIRECT - Manual trigger");
        
        $this->user->portfolio->update([
            'real_trading_active' => true,
            'real_trading_enabled' => true
        ]);
        
        $this->forceLoadUserData();
        $this->loadPendingOrders();
        $this->loadBinancePositions();
        session()->flash('message', 'Force redirect to dashboard completed!');
    }

    /**
     * Validation rules
     */
    protected function rules()
    {
        return [
            'api_key' => 'required|string|min:20',
            'api_secret' => 'required|string|min:20'
        ];
    }

    /**
     * Custom validation messages
     */
    protected function messages()
    {
        return [
            'api_key.required' => 'API Key is required',
            'api_secret.required' => 'API Secret is required',
            'api_key.min' => 'API Key must be at least 20 characters',
            'api_secret.min' => 'API Secret must be at least 20 characters'
        ];
    }

    public function render()
    {
        Log::info("🎯 RENDER - Current State", [
            'user_id' => $this->user->id,
            'has_subscription' => $this->hasRealSubscription,
            'binance_connected' => $this->binanceConnected,
            'trading_enabled' => $this->realTradingEnabled,
            'pending_orders' => $this->pendingOrdersCount,
            'binance_positions' => $this->activePositionsCount,
            'total_unrealized_pnl' => $this->totalUnrealizedPnl,
            'active_tab' => $this->activeTab
        ]);

        return view('livewire.real-trading-page')->layout('layouts.app');
    }
}