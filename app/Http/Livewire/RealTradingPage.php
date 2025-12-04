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
use App\Services\Cache\TradingCacheService;
use App\Jobs\RefreshUserDataJob;

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
    public $loading = false; 
    public $fromCache = false;
    public $lastCacheUpdate = null;
    public $cacheStats = [];
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
    private $tradingService;
    private $tradingCache;
    // Real trading execution service
    protected $realTradingService;

    public function boot()
    {
        $this->realTradingService = app(RealTradingExecutionService::class);
        $this->tradingService = app(RealTradingExecutionService::class);
        $this->tradingCache = app(TradingCacheService::class);
    }

    public function mount()
    {
        $this->user = Auth::user();
        $this->forceLoadUserData();
        $this->loadUserAccounts();
        $this->loadPendingOrders();
        
        // ✅ OPTIMIZATION: Load positions dengan cache-first approach
        // Tapi cek dulu apakah user punya Binance account
        if ($this->binanceConnected && $this->hasActiveBinanceAccount()) {
            $this->loadCachedPositionsFirst();
        } else {
            // Jika tidak punya account, set empty positions
            $this->binancePositions = [];
            $this->activePositionsCount = 0;
            $this->totalUnrealizedPnl = 0;
            $this->fromCache = false;
            
            Log::warning("User {$this->user->id} tidak memiliki Binance account aktif, skipping position load");
        }
        
        Log::info("🔍 MOUNT - Initial State", [
            'user_id' => $this->user->id,
            'has_subscription' => $this->hasRealSubscription,
            'binance_connected' => $this->binanceConnected,
            'trading_enabled' => $this->realTradingEnabled,
            'has_active_account' => $this->hasActiveBinanceAccount(),
            'pending_orders' => $this->pendingOrdersCount,
            'binance_positions' => count($this->binancePositions),
            'from_cache' => $this->fromCache ?? false
        ]);
    }
    /**
     * ✅ NEW METHOD: Load positions dari cache dulu, jika tidak ada baru dari Binance
     */
    public function loadCachedPositionsFirst()
    {
        try {
            // Cek cache dulu
            $cachedPositions = $this->tradingCache->getPositions($this->user->id);
            
            if (!empty($cachedPositions)) {
                $this->binancePositions = $cachedPositions;
                $this->activePositionsCount = count($cachedPositions);
                $this->totalUnrealizedPnl = array_sum(array_column($cachedPositions, 'unrealized_pnl'));
                $this->fromCache = true;
                
                Log::debug("✅ Positions loaded from cache", [
                    'user_id' => $this->user->id,
                    'count' => $this->activePositionsCount
                ]);
                
                // Trigger background refresh untuk update data terbaru
                $this->triggerBackgroundPositionRefresh();
                
            } else {
                // Jika cache kosong, load dari Binance
                $this->loadBinancePositions();
                $this->fromCache = false;
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to load cached positions: " . $e->getMessage(), [
                'user_id' => $this->user->id
            ]);
            
            // Fallback langsung ke Binance API
            $this->loadBinancePositions();
            $this->fromCache = false;
        }
    }
    /**
     * ✅ NEW: Check if user has active Binance account
     */
    public function hasActiveBinanceAccount(): bool
    {
        try {
            return $this->user->binanceAccounts()
                ->active()
                ->verified()
                ->exists();
        } catch (\Exception $e) {
            Log::warning("Failed to check Binance account: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NEW: Get Binance account status
     */
    public function getBinanceAccountStatus(): array
    {
        try {
            $accounts = $this->user->binanceAccounts()
                ->select(['is_testnet', 'is_active', 'verification_status', 'created_at'])
                ->get();
            
            return [
                'has_accounts' => $accounts->isNotEmpty(),
                'active_accounts' => $accounts->where('is_active', true)->count(),
                'verified_accounts' => $accounts->where('verification_status', true)->count(),
                'testnet_accounts' => $accounts->where('is_testnet', true)->count(),
                'mainnet_accounts' => $accounts->where('is_testnet', false)->count(),
                'accounts' => $accounts
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get Binance account status: " . $e->getMessage());
            return ['has_accounts' => false];
        }
    }
    /**
     * ✅ NEW METHOD: Trigger background refresh untuk positions
     */
    private function triggerBackgroundPositionRefresh()
    {
        try {
            if ($this->binanceConnected && $this->realTradingEnabled) {
                RefreshUserDataJob::dispatch($this->user->id)
                    ->onQueue('sync')
                    ->delay(now()->addSeconds(2));
                
                Log::debug("✅ Background refresh triggered for user {$this->user->id}");
            }
        } catch (\Exception $e) {
            Log::warning("Failed to trigger background refresh: " . $e->getMessage());
        }
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

                // ✅ TAMBAHKAN LOGGING DETAIL
                $accountStatus = $this->getBinanceAccountStatus();
                
                Log::info("✅ FORCE LOAD - Data Loaded", [
                    'user_id' => $this->user->id,
                    'has_subscription' => $this->hasRealSubscription,
                    'binance_connected' => $this->binanceConnected,
                    'trading_enabled' => $this->realTradingEnabled,
                    'real_balance' => $this->realBalance,
                    'is_testnet' => $this->isTestnet,
                    'has_active_account' => $this->hasActiveBinanceAccount(),
                    'account_status' => $accountStatus
                ]);
                
            } else {
                $this->resetToDefaultState();
                Log::warning("❌ FORCE LOAD - No Portfolio", [
                    'user_id' => $this->user->id,
                    'has_active_account' => $this->hasActiveBinanceAccount()
                ]);
            }

        } catch (\Exception $e) {
            Log::error("❌ FORCE LOAD - Error", [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
     * Optimized method untuk load positions dengan cache
     */
    public function getPositionsProperty()
    {
        // Jika sudah ada positions yang di-load, gunakan itu
        if (!empty($this->binancePositions)) {
            return $this->binancePositions;
        }
        
        // Coba dari cache dulu
        try {
            $cachedPositions = $this->tradingCache->getPositions($this->user->id);
            
            if (!empty($cachedPositions)) {
                $this->fromCache = true;
                return $cachedPositions;
            }
        } catch (\Exception $e) {
            Log::warning("Cache read failed: " . $e->getMessage());
        }
        
        // Fallback ke empty array
        return [];
    }   
 
    /**
     * Get trading statistics
     */
    public function getTradingStats()
    {
        try {
            if ($this->tradingService && method_exists($this->tradingService, 'getTradingStatistics')) {
                return $this->tradingService->getTradingStatistics($this->user->id);
            }
            return [];
        } catch (\Exception $e) {
            Log::error("Failed to get trading stats: " . $e->getMessage());
            return [];
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
                                    $sq->whereNull('status')
                                       ->orWhereNotIn('status', ['FILLED', 'CANCELLED']);
                                });
                          });
                })
                ->where(function($query) {
                    $query->whereNull('status')
                          ->orWhereNotIn('status', ['FILLED', 'CANCELLED']);
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
                        'status' => $order->status,
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
     * Load trading positions dari Binance API dengan CACHE optimization & proper error handling
     */
    public function loadBinancePositions()
    {
        $this->loadingPositions = true;
        
        try {
            // ✅ CEK DULU: Apakah user memiliki Binance account yang aktif?
            if (!$this->binanceConnected) {
                $this->binancePositions = [];
                $this->activePositionsCount = 0;
                $this->totalUnrealizedPnl = 0;
                
                Log::warning("User {$this->user->id} tidak memiliki koneksi Binance aktif");
                $this->loadingPositions = false;
                return;
            }
            
            // ✅ CEK CACHE DULU - Optimization
            $cachedPositions = $this->tradingCache->getPositions($this->user->id);
            
            if (!empty($cachedPositions)) {
                $this->binancePositions = $cachedPositions;
                $this->activePositionsCount = count($cachedPositions);
                $this->totalUnrealizedPnl = array_sum(array_column($cachedPositions, 'unrealized_pnl'));
                
                Log::info("📊 POSITIONS FROM CACHE", [
                    'user_id' => $this->user->id,
                    'count' => $this->activePositionsCount,
                    'total_pnl' => $this->totalUnrealizedPnl
                ]);
                
                $this->loadingPositions = false;
                return; // ✅ RETURN EARLY - Tidak perlu hit API
            }
            
            Log::info("🔄 FETCHING FROM BINANCE - Cache miss for user {$this->user->id}");
            
            // ✅ CEK: Apakah user memiliki Binance account sebelum membuat instance
            $binanceService = app(BinanceAccountService::class);
            
            // Cek dulu apakah user punya account aktif
            $hasActiveAccount = $this->user->binanceAccounts()
                ->active()
                ->verified()
                ->exists();
            
            if (!$hasActiveAccount) {
                Log::warning("User {$this->user->id} tidak memiliki Binance account aktif");
                
                // Set empty positions
                $this->binancePositions = [];
                $this->activePositionsCount = 0;
                $this->totalUnrealizedPnl = 0;
                
                // Cache empty positions untuk menghindari request berulang
                $this->tradingCache->cachePositions($this->user->id, []);
                
                $this->loadingPositions = false;
                return;
            }
            
            try {
                // ✅ DAPATKAN BINANCE INSTANCE dengan error handling
                $binance = $binanceService->getBinanceInstance($this->user->id);
                
                if (!$binance) {
                    throw new \Exception("Failed to initialize Binance instance");
                }
                
            } catch (\Exception $e) {
                Log::error("❌ Binance Instance Creation Failed", [
                    'user_id' => $this->user->id,
                    'error' => $e->getMessage()
                ]);
                
                // Set empty positions
                $this->binancePositions = [];
                $this->activePositionsCount = 0;
                $this->totalUnrealizedPnl = 0;
                
                // Cache empty positions dengan TTL pendek (1 menit)
                $this->tradingCache->cachePositions($this->user->id, []);
                
                $this->loadingPositions = false;
                return;
            }
            
            // ✅ JIKA BERHASIL DAPAT INSTANCE, LANJUT FETCH DATA
            $positionsData = [];
            $fetchError = null;
            
            // ✅ FIX: Coba futuresAccount() dengan error handling yang lebih baik
            if (method_exists($binance, 'futuresAccount')) {
                try {
                    $accountInfo = $binance->futuresAccount();
                    
                    // ✅ CEK JIKA RESPONSE ADALAH ERROR DARI BINANCE
                    if (isset($accountInfo['code']) && isset($accountInfo['msg'])) {
                        Log::error("❌ Binance API Error: {$accountInfo['code']} - {$accountInfo['msg']}");
                        $fetchError = "Binance API Error: {$accountInfo['msg']} (Code: {$accountInfo['code']})";
                    } 
                    // ✅ RESPONSE VALID
                    elseif (isset($accountInfo['positions']) && is_array($accountInfo['positions'])) {
                        $positionsData = $accountInfo['positions'];
                        Log::info("✅ Found positions in futuresAccount[positions]", ['count' => count($positionsData)]);
                    } 
                    // ✅ COBA CARI POSITIONS DI STRUKTUR LAIN
                    else {
                        foreach ($accountInfo as $key => $value) {
                            if (is_array($value) && count($value) > 0) {
                                $firstItem = $value[0] ?? null;
                                if (is_array($firstItem) && isset($firstItem['symbol'])) {
                                    $positionsData = $value;
                                    Log::info("✅ Found positions in key: {$key}", ['count' => count($positionsData)]);
                                    break;
                                }
                            }
                        }
                    }
                    
                } catch (\Exception $e) {
                    $fetchError = $e->getMessage();
                    Log::warning("⚠️ futuresAccount() failed: " . $fetchError);
                    
                    // ✅ CEK JIKA ERROR -2015 (INVALID PERMISSION)
                    if (strpos($fetchError, '-2015') !== false || strpos($fetchError, 'Invalid API-key') !== false) {
                        Log::error("❌ API KEY MISSING FUTURES PERMISSION (Code -2015)");
                        // Cache empty positions dengan note permission error
                        $this->tradingCache->cachePositions($this->user->id, [], 60); // 1 menit TTL
                    }
                }
            }
            
            // ✅ FIX: HAPUS FALLBACK KE account() - TIDAK PERLU KARENA HANYA BUTUH FUTURES
            // if (empty($positionsData) && method_exists($binance, 'account')) {
            //    HAPUS BLOK INI SEPENUHNYA!
            // }
            
            // ✅ JIKA TIDAK ADA DATA, LOG DAN LANJUT
            if (empty($positionsData)) {
                Log::info("ℹ️ No futures positions found for user {$this->user->id}. " . 
                         ($fetchError ? "Error: {$fetchError}" : "User has no open positions."));
            }
            
            // Filter dan format positions
            $formattedPositions = [];
            $totalUnrealizedPnl = 0;
            
            foreach ($positionsData as $position) {
                if (!is_array($position)) continue;
                
                // Cek berbagai kemungkinan field untuk amount
                $positionAmt = 0;
                if (isset($position['positionAmt'])) {
                    $positionAmt = (float) $position['positionAmt'];
                } elseif (isset($position['positionAmount'])) {
                    $positionAmt = (float) $position['positionAmount'];
                } elseif (isset($position['amount'])) {
                    $positionAmt = (float) $position['amount'];
                } elseif (isset($position['quantity'])) {
                    $positionAmt = (float) $position['quantity'];
                }
                
                // Skip jika amount 0
                if (abs($positionAmt) < 0.000001) continue; // Lebih fleksibel
                
                $symbol = $position['symbol'] ?? '';
                if (empty($symbol)) continue;
                
                // Format data position
                $formattedPosition = [
                    'symbol' => $symbol,
                    'positionAmt' => $positionAmt,
                    'entryPrice' => (float) ($position['entryPrice'] ?? $position['avgPrice'] ?? 0),
                    'markPrice' => (float) ($position['markPrice'] ?? $position['currentPrice'] ?? 0),
                    'unRealizedProfit' => (float) ($position['unRealizedProfit'] ?? $position['unrealizedProfit'] ?? $position['pnl'] ?? 0),
                    'leverage' => (int) ($position['leverage'] ?? 1),
                    'liquidationPrice' => (float) ($position['liquidationPrice'] ?? 0),
                    'marginType' => $position['marginType'] ?? 'isolated',
                    'isolatedMargin' => (float) ($position['isolatedMargin'] ?? 0),
                    'positionSide' => $position['positionSide'] ?? 'BOTH'
                ];
                
                // Tentukan side berdasarkan amount
                $side = $positionAmt > 0 ? 'BUY' : 'SELL';
                $positionType = $positionAmt > 0 ? 'LONG' : 'SHORT';
                $quantity = abs($positionAmt);
                
                // Ambil harga current jika markPrice 0
                $currentPrice = $formattedPosition['markPrice'];
                if ($currentPrice <= 0) {
                    try {
                        $prices = $binance->prices();
                        $searchSymbol = str_replace('_', '', $symbol);
                        if (isset($prices[$searchSymbol])) {
                            $currentPrice = (float) $prices[$searchSymbol];
                        }
                    } catch (\Exception $e) {
                        $currentPrice = $formattedPosition['entryPrice'] > 0 ? $formattedPosition['entryPrice'] : 1;
                    }
                }
                
                // Hitung P&L percentage
                $pnl = $formattedPosition['unRealizedProfit'];
                $pnlPercentage = 0;
                if ($formattedPosition['entryPrice'] > 0 && $quantity > 0) {
                    $positionValue = $formattedPosition['entryPrice'] * $quantity;
                    if ($positionValue > 0) {
                        $pnlPercentage = ($pnl / $positionValue) * 100;
                    }
                }
                
                $formattedPositions[] = [
                    'symbol' => $symbol,
                    'side' => $side,
                    'position_type' => $positionType,
                    'entry_price' => $formattedPosition['entryPrice'],
                    'mark_price' => $currentPrice,
                    'quantity' => $quantity,
                    'unrealized_pnl' => $pnl,
                    'pnl_percentage' => $pnlPercentage,
                    'leverage' => $formattedPosition['leverage'],
                    'liquidation_price' => $formattedPosition['liquidationPrice'],
                    'margin_type' => $formattedPosition['marginType'],
                    'isolated_margin' => $formattedPosition['isolatedMargin'],
                    'position_side' => $formattedPosition['positionSide'],
                    'updated_at' => now()->timestamp
                ];
                
                $totalUnrealizedPnl += $pnl;
            }
            
            // Sort by P&L
            usort($formattedPositions, function($a, $b) {
                return $a['unrealized_pnl'] <=> $b['unrealized_pnl'];
            });
            
            $this->binancePositions = $formattedPositions;
            $this->activePositionsCount = count($formattedPositions);
            $this->totalUnrealizedPnl = $totalUnrealizedPnl;
            
            // ✅ SIMPAN KE CACHE - Optimization
            if (!empty($formattedPositions)) {
                $this->tradingCache->cachePositions($this->user->id, $formattedPositions);
                Log::debug("✅ Positions cached for user {$this->user->id}");
            } else {
                // Cache empty array jika tidak ada positions
                $this->tradingCache->cachePositions($this->user->id, []);
            }
            
            Log::info("📊 BINANCE POSITIONS LOADED & CACHED", [
                'user_id' => $this->user->id,
                'count' => $this->activePositionsCount,
                'total_unrealized_pnl' => $totalUnrealizedPnl,
                'cached' => true,
                'had_error' => !empty($fetchError)
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Failed to load Binance positions: " . $e->getMessage(), [
                'user_id' => $this->user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback ke empty array
            $this->binancePositions = [];
            $this->activePositionsCount = 0;
            $this->totalUnrealizedPnl = 0;
            
            // Cache empty positions untuk menghindari request berulang
            try {
                $this->tradingCache->cachePositions($this->user->id, []);
            } catch (\Exception $cacheException) {
                Log::warning("Failed to cache empty positions: " . $cacheException->getMessage());
            }
        }
        
        $this->loadingPositions = false;
    }
    /**
     * Refresh pending orders and auto-clean filled ones dengan cache optimization
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
                    
                    // Cek status di Binance menggunakan futuresGetOrder
                    $binanceStatus = $binance->futuresOrderStatus(
                        $order->symbol,
                        ['orderId' => $order->binance_order_id]
                    );
                    
                    $orderStatus = $binanceStatus['status'] ?? 'UNKNOWN';
                    $executedQty = $binanceStatus['executedQty'] ?? 0;
                    $avgPrice = $binanceStatus['avgPrice'] ?? 0;
                    
                    // Update status lokal
                    $order->update([
                        'status' => $orderStatus,
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
            
            // ✅ INVALIDATE CACHE
            $this->tradingCache->invalidateUserCache($this->user->id);
            
            // Reload data
            $this->loadPendingOrders();
            $this->loadBinancePositions();
            
            // ✅ TRIGGER BACKGROUND REFRESH
            RefreshUserDataJob::dispatch($this->user->id);
            
            // Tampilkan message
            $message = 'Orders refreshed! Cache invalidated.';
            if ($filledCount > 0) $message .= " {$filledCount} order(s) filled.";
            if ($cancelledCount > 0) $message .= " {$cancelledCount} order(s) cancelled.";
            if ($expiredCount > 0) $message .= " {$expiredCount} order(s) expired.";
            
            session()->flash('message', $message);
            
            Log::info("🔄 Orders refreshed with cache invalidation", [
                'user_id' => $this->user->id,
                'filled' => $filledCount,
                'cancelled' => $cancelledCount,
                'expired' => $expiredCount
            ]);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Refresh failed: ' . $e->getMessage());
            Log::error("Refresh pending orders failed: " . $e->getMessage());
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
            
            // Get current price
            $ticker = $binance->prices();
            $currentPrice = null;
            
            $tickerSymbol = str_replace('_', '', $symbol);
            if (isset($ticker[$tickerSymbol])) {
                $currentPrice = (float) $ticker[$tickerSymbol];
            } elseif (isset($ticker[$symbol])) {
                $currentPrice = (float) $ticker[$symbol];
            } else {
                $currentPrice = $positionData['mark_price'] ?? $positionData['entry_price'] ?? 0;
            }
            
            Log::info("📤 Closing Position", [
                'symbol' => $symbol,
                'side' => $orderSide,
                'quantity' => $quantity,
                'current_price' => $currentPrice
            ]);
            
            // Place market order to close position
            // Gunakan method yang tersedia di jaggedsoft
            if (method_exists($binance, 'futuresMarket')) {
                $order = $binance->futuresMarket($symbol, $orderSide, $quantity, [
                    'reduceOnly' => true
                ]);
            } elseif (method_exists($binance, 'futuresOrder')) {
                $order = $binance->futuresOrder($symbol, $orderSide, $quantity, 0, 'MARKET', [
                    'reduceOnly' => true
                ]);
            } elseif (method_exists($binance, 'order')) {
                $order = $binance->order($symbol, $orderSide, $quantity, 0, 'MARKET');
            } else {
                throw new \Exception("No order method available");
            }
            
            Log::info("✅ Close Order Placed", [
                'order_id' => $order['orderId'] ?? 'N/A',
                'status' => $order['status'] ?? 'N/A'
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
            $orderStatus = $binance->futuresOrderStatus(
                $order->symbol,
                ['orderId' => $order->binance_order_id]
            );

            
            // Update local status
            $order->update([
                'status' => $orderStatus['status'] ?? 'UNKNOWN',
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
            
            // Cancel order di Binance menggunakan futuresCancel
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
            'order_status_color' => $this->getOrderStatusBadgeColor($order['status'] ?? '')
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
     * Refresh semua data dengan cache optimization
     */
    public function refreshData()
    {
        $this->loading = true;
        
        try {
            // ✅ INVALIDATE CACHE DULU
            $this->tradingCache->invalidateUserCache($this->user->id);
            
            $this->forceLoadUserData();
            $this->loadUserAccounts();
            $this->loadPendingOrders();
            $this->loadBinancePositions();
            
            // ✅ TRIGGER BACKGROUND REFRESH UNTUK UPDATE DATA TERBARU
            RefreshUserDataJob::dispatch($this->user->id)
                ->onQueue('sync')
                ->delay(now()->addSeconds(1));
            
            session()->flash('message', 'All data refreshed successfully! Cache invalidated.');
            
            Log::info("🔄 MANUAL REFRESH with cache invalidation", [
                'user_id' => $this->user->id
            ]);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Refresh failed: ' . $e->getMessage());
            Log::error("Refresh data failed: " . $e->getMessage());
        }
        
        $this->loading = false;
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
    /**
     * ✅ NEW: Get cached balance
     */
    public function getCachedBalance()
    {
        try {
            $balance = $this->tradingCache->getBalance($this->user->id);
            if ($balance && isset($balance['total'])) {
                return [
                    'total' => $balance['total'],
                    'available' => $balance['available'] ?? 0,
                    'cached' => true,
                    'timestamp' => $balance['timestamp'] ?? null
                ];
            }
        } catch (\Exception $e) {
            Log::warning("Failed to get cached balance: " . $e->getMessage());
        }
        
        return [
            'total' => $this->realBalance,
            'available' => $this->futuresBalance,
            'cached' => false,
            'timestamp' => null
        ];
    }

    /**
     * ✅ NEW: Force cache refresh
     */
    public function forceCacheRefresh()
    {
        $this->loading = true;
        
        try {
            // Invalidate semua cache
            $this->tradingCache->invalidateUserCache($this->user->id);
            
            // Trigger background refresh
            RefreshUserDataJob::dispatch($this->user->id)
                ->onQueue('trading');
            
            // Load fresh data
            $this->loadBinancePositions();
            $this->loadPendingOrders();
            
            session()->flash('message', 'Cache refreshed! Data will update shortly.');
            
            Log::info("🔄 Force cache refresh for user {$this->user->id}");
            
        } catch (\Exception $e) {
            session()->flash('error', 'Cache refresh failed: ' . $e->getMessage());
            Log::error("Force cache refresh failed: " . $e->getMessage());
        }
        
        $this->loading = false;
    }

    /**
     * ✅ NEW: Get cache statistics
     */
    public function getCacheStats()
    {
        try {
            $this->cacheStats = $this->tradingCache->getStats();
            $this->lastCacheUpdate = now()->format('H:i:s');
        } catch (\Exception $e) {
            Log::warning("Failed to get cache stats: " . $e->getMessage());
            $this->cacheStats = [];
        }
    }

    /**
     * ✅ NEW: Update balance dengan cache
     */
    public function refreshBalanceWithCache()
    {
        $this->refreshing = true;
        
        try {
            $binanceService = app(BinanceAccountService::class);
            $result = $binanceService->updateBalanceSnapshot($this->user->id);
            
            if ($result) {
                // Invalidate balance cache
                $this->tradingCache->invalidateUserCache($this->user->id);
                
                $this->forceLoadUserData();
                session()->flash('message', 'Balance updated! Cache invalidated.');
            } else {
                session()->flash('error', 'Failed to update balance');
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
        
        $this->refreshing = false;
    }
    public function render()
    {
        // Get cache stats untuk debugging
        if (config('app.debug')) {
            $this->getCacheStats();
        }
        
        Log::info("🎯 RENDER - Current State", [
            'user_id' => $this->user->id,
            'has_subscription' => $this->hasRealSubscription,
            'binance_connected' => $this->binanceConnected,
            'trading_enabled' => $this->realTradingEnabled,
            'pending_orders' => $this->pendingOrdersCount,
            'binance_positions' => $this->activePositionsCount,
            'total_unrealized_pnl' => $this->totalUnrealizedPnl,
            'active_tab' => $this->activeTab,
            'from_cache' => $this->fromCache,
            'cache_stats' => $this->cacheStats ? 'available' : 'none'
        ]);

        return view('livewire.real-trading-page')->layout('layouts.app');
    }
}