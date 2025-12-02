<?php
// app/Http/Livewire/RealTradingPage.php
namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\PendingOrder;
use App\Services\BinanceAccountService;
use App\Services\RealTradingExecutionService;
use Illuminate\Support\Facades\Log;

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

    // Confirmation modals
    public $showCancelConfirm = false;
    public $orderToCancel = null;

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
        
        Log::info("🔍 MOUNT - Initial State", [
            'user_id' => $this->user->id,
            'has_subscription' => $this->hasRealSubscription,
            'binance_connected' => $this->binanceConnected,
            'trading_enabled' => $this->realTradingEnabled,
            'pending_orders' => $this->pendingOrdersCount
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
                ->whereIn('status', ['PENDING', 'PARTIALLY_FILLED'])
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
     * Refresh pending orders
     */
    public function refreshPendingOrders()
    {
        $this->refreshingOrders = true;
        
        try {
            // Check expired orders via service
            $expiredCount = $this->realTradingService->checkPendingOrders();
            
            if ($expiredCount > 0) {
                session()->flash('info', "{$expiredCount} expired orders cancelled");
            }
            
            // Reload orders
            $this->loadPendingOrders();
            
            session()->flash('message', 'Pending orders refreshed!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Refresh failed: ' . $e->getMessage());
        }
        
        $this->refreshingOrders = false;
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
            
            session()->flash('info', 
                "Order Status: {$orderStatus['status']} | " .
                "Filled: {$orderStatus['executedQty']}/{$orderStatus['origQty']}"
            );
            
        } catch (\Exception $e) {
            session()->flash('error', 'Status check failed: ' . $e->getMessage());
        }
    }

    /**
     * Get order summary
     */
    public function getOrderSummary($order)
    {
        $totalValue = $order['limit_price'] * $order['quantity'];
        $timeLeft = $order['expires_at'] 
            ? now()->diffInMinutes(\Carbon\Carbon::parse($order['expires_at']), false)
            : null;
        
        return [
            'total_value' => number_format($totalValue, 2),
            'time_left' => $timeLeft > 0 ? $timeLeft . ' minutes' : 'Expired',
            'is_expired' => $timeLeft !== null && $timeLeft <= 0,
            'badge_color' => $this->getStatusBadgeColor($order['status'])
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

    /**
     * Account Management Methods
     */
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

    public function refreshData()
    {
        $this->forceLoadUserData();
        $this->loadUserAccounts();
        $this->loadPendingOrders();
        session()->flash('message', 'Data refreshed successfully!');
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
            'should_show_dashboard' => $this->hasRealSubscription && $this->binanceConnected
        ]);

        return view('livewire.real-trading-page')->layout('layouts.app');
    }
}