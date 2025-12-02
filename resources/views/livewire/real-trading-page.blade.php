<!-- resources/views/livewire/real-trading-page.blade.php -->
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-8"
     x-data="tradingDashboard()"
     x-init="init()"
     @trading-data-updated.window="onTradingDataUpdated($event.detail)">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <!-- Header dengan Cache Status -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Real Trading</h1>
            <p class="text-gray-600 mt-2">Live trading with your Binance account</p>
            
            <!-- Cache Status Indicator -->
            <div class="mt-2 inline-flex items-center space-x-2" wire:loading.class="opacity-50">
                <div class="flex items-center space-x-1 text-xs">
                    <template x-if="!loading && cacheStatus.cached">
                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-bolt mr-1 text-xs"></i>
                            <span>Live Data</span>
                            <span class="ml-1 text-xs opacity-75" x-text="`(${cacheStatus.age}s ago)`"></span>
                        </span>
                    </template>
                    <template x-if="!loading && !cacheStatus.cached">
                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                            <i class="fas fa-sync mr-1 text-xs"></i>
                            <span>Loading...</span>
                        </span>
                    </template>
                    <template x-if="loading">
                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                            <i class="fas fa-spinner fa-spin mr-1 text-xs"></i>
                            <span>Refreshing...</span>
                        </span>
                    </template>
                </div>
                
                <!-- Auto-refresh Toggle -->
                <button @click="toggleAutoRefresh()" 
                        class="text-xs text-gray-500 hover:text-gray-700 flex items-center space-x-1">
                    <i class="fas" :class="autoRefresh ? 'fa-toggle-on text-green-500' : 'fa-toggle-off'"></i>
                    <span x-text="autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh OFF'"></span>
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 animate-fade-in">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 animate-fade-in">
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl mb-6 animate-fade-in">
                {{ session('info') }}
            </div>
        @endif

        <!-- Real-time Update Notification -->
        <div x-show="showUpdateNotification" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 max-w-sm">
            <div class="bg-blue-500 text-white px-4 py-3 rounded-xl shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-sync-alt animate-spin"></i>
                        <span class="font-semibold">Data Updated</span>
                    </div>
                    <button @click="showUpdateNotification = false" class="text-white hover:text-blue-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-sm mt-1 opacity-90" x-text="updateMessage"></p>
            </div>
        </div>

        <!-- Connection Status Badge -->
        <div wire:loading.class="opacity-50" class="mb-4">
            <div class="flex items-center justify-center space-x-2">
                <div class="relative">
                    <div class="w-3 h-3 rounded-full" 
                         :class="{
                             'bg-green-500 animate-pulse': @this.binanceConnected,
                             'bg-red-500': !@this.binanceConnected
                         }"></div>
                    <div class="absolute inset-0 bg-green-500 rounded-full animate-ping" 
                         x-show="@this.binanceConnected"></div>
                </div>
                <span class="text-sm font-semibold" 
                      :class="{
                          'text-green-600': @this.binanceConnected,
                          'text-red-600': !@this.binanceConnected
                      }">
                    <template x-if="@this.binanceConnected">
                        ✅ Connected to Binance {{ $isTestnet ? 'Testnet' : 'Live' }}
                    </template>
                    <template x-if="!@this.binanceConnected">
                        ❌ Not Connected
                    </template>
                </span>
            </div>
        </div>

        <!-- Conditional Content -->
        @if(!$hasRealSubscription)
            <!-- UPGRADE CARD -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 mb-6 animate-slide-up">
                <!-- ... (keep existing upgrade card content) ... -->
            </div>

        @elseif(!$binanceConnected)
            <!-- API CONNECTION CARD -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 animate-slide-up">
                <!-- ... (keep existing connection card content) ... -->
            </div>

        @else
            <!-- REAL TRADING DASHBOARD -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 animate-slide-up">
                
                <!-- Header dengan Account Management -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center space-x-2 bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span>BINANCE CONNECTED • {{ $isTestnet ? 'TESTNET' : 'LIVE TRADING' }}</span>
                        
                        <!-- Cache Badge -->
                        @if($fromCache)
                            <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full flex items-center">
                                <i class="fas fa-database mr-1 text-xs"></i>
                                Cached
                            </span>
                        @endif
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Real Trading Dashboard</h2>
                    <p class="text-gray-600">
                        {{ $isTestnet ? 'Testing with fake money' : 'Live trading with real money' }}
                    </p>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-center space-x-3 mt-4">
                        <!-- Account Management Button -->
                        <button 
                            wire:click="toggleAccountManagement"
                            class="inline-flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-xl transition-colors"
                        >
                            <i class="fas fa-cog"></i>
                            <span>Manage API Keys</span>
                        </button>
                        
                        <!-- Cache Refresh Button -->
                        <button 
                            wire:click="forceCacheRefresh"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center space-x-2 bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-xl transition-colors disabled:opacity-50"
                            title="Force refresh cache data"
                        >
                            @if($loading)
                                <i class="fas fa-spinner fa-spin"></i>
                            @else
                                <i class="fas fa-sync-alt"></i>
                            @endif
                            <span>Refresh Cache</span>
                        </button>
                    </div>
                </div>

                <!-- Environment Warning -->
                <div class="mb-6 p-4 rounded-xl border 
                    @if($isTestnet) 
                        bg-yellow-50 border-yellow-200 
                    @else 
                        bg-red-50 border-red-200 
                    @endif"
                    wire:loading.class="opacity-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            @if($isTestnet)
                                <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                                <div>
                                    <strong>🔧 TESTNET MODE</strong> 
                                    <span class="text-yellow-700">- Trading with fake money</span>
                                </div>
                            @else
                                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                                <div>
                                    <strong>🚀 LIVE TRADING</strong> 
                                    <span class="text-red-700">- Real money at risk!</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Balance dengan Cache Status -->
                        <div class="text-right" x-data="{ balance: @entangle('futuresBalance') }">
                            <div class="text-lg font-bold" x-text="'$' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(balance)"></div>
                            <div class="text-xs {{ $futuresBalance >= $minBalanceRequired ? 'text-green-600' : 'text-red-600' }} font-semibold flex items-center justify-end">
                                @if($futuresBalance >= $minBalanceRequired)
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <span>✅ Sufficient balance</span>
                                @else
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <span>⚠️ Min: ${{ $minBalanceRequired }}</span>
                                @endif
                                @if($fromCache)
                                    <i class="fas fa-database ml-1 text-blue-500" title="Data from cache"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trading Control Card dengan Real-time Updates -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 mb-6"
                     wire:loading.class="opacity-50">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-lg font-semibold text-gray-900">Trading Control</h3>
                            <p class="text-gray-600 text-sm">Enable/disable {{ $isTestnet ? 'testnet' : 'real money' }} trading</p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <!-- Balance Display -->
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-900">${{ number_format($futuresBalance, 2) }}</div>
                                <div class="text-xs {{ $futuresBalance >= $minBalanceRequired ? 'text-green-600' : 'text-red-600' }} font-semibold flex items-center justify-end">
                                    @if($futuresBalance >= $minBalanceRequired)
                                        <i class="fas fa-check-circle mr-1"></i>
                                        <span>✅ Sufficient</span>
                                    @else
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        <span>⚠️ Min: ${{ $minBalanceRequired }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Trading Toggle Switch -->
                            <button 
                                wire:click="toggleRealTrading"
                                wire:loading.attr="disabled"
                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 
                                       {{ $realTradingEnabled ? 'bg-green-500' : 'bg-gray-200' }} 
                                       {{ $futuresBalance < $minBalanceRequired ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $futuresBalance < $minBalanceRequired ? 'disabled' : '' }}
                            >
                                <span class="sr-only">Enable trading</span>
                                <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform 
                                            {{ $realTradingEnabled ? 'translate-x-6' : 'translate-x-1' }}" />
                            </button>
                        </div>
                    </div>

                    <!-- Status Badge dengan WebSocket Indicator -->
                    <div class="mt-4 flex justify-center">
                        @if($realTradingEnabled)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-play-circle mr-2"></i>
                                TRADING ACTIVE - AI is executing {{ $isTestnet ? 'test' : 'real' }} trades
                                <div class="ml-2 w-2 h-2 bg-green-500 rounded-full animate-pulse" 
                                     x-show="websocketConnected"></div>
                            </span>
                        @elseif($futuresBalance < $minBalanceRequired)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                INSUFFICIENT BALANCE - 
                                @if($isTestnet)
                                    Visit testnet.binancefuture.com for test funds
                                @else
                                    Transfer funds to Futures wallet
                                @endif
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-pause-circle mr-2"></i>
                                TRADING PAUSED - Enable to start trading
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons dengan Loading States -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <button 
                        wire:click="refreshBalanceWithCache"
                        wire:loading.attr="disabled"
                        class="flex items-center justify-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold transition-colors disabled:opacity-50"
                    >
                        @if($refreshing)
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            <span>Refreshing...</span>
                        @else
                            <i class="fas fa-sync-alt mr-2"></i>
                            <span>Refresh Balance</span>
                        @endif
                        @if($fromCache)
                            <i class="fas fa-database text-blue-200" title="From cache"></i>
                        @endif
                    </button>
                    
                    <a 
                        href="{{ $isTestnet ? 'https://testnet.binancefuture.com' : 'https://www.binance.com/en/futures/transfer' }}" 
                        target="_blank"
                        class="flex items-center justify-center space-x-2 bg-green-500 hover:bg-green-600 text-white py-3 px-4 rounded-xl font-semibold transition-colors text-center"
                    >
                        <i class="fas fa-exchange-alt mr-2"></i>
                        <span>
                            @if($isTestnet)
                                Get Test Funds
                            @else
                                Transfer Funds
                            @endif
                        </span>
                    </a>
                    
                    <button 
                        wire:click="refreshPositions"
                        wire:loading.attr="disabled"
                        class="flex items-center justify-center space-x-2 bg-purple-500 hover:bg-purple-600 text-white py-3 px-4 rounded-xl font-semibold transition-colors disabled:opacity-50"
                    >
                        @if($loadingPositions)
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            <span>Loading...</span>
                        @else
                            <i class="fas fa-chart-line mr-2"></i>
                            <span>Refresh Positions</span>
                        @endif
                    </button>
                    
                    <button 
                        wire:click="refreshData"
                        wire:loading.attr="disabled"
                        class="flex items-center justify-center space-x-2 bg-gray-500 hover:bg-gray-600 text-white py-3 px-4 rounded-xl font-semibold transition-colors disabled:opacity-50"
                    >
                        <i class="fas fa-redo mr-2"></i>
                        <span>Refresh All</span>
                    </button>
                </div>

                <!-- Pending Orders Section dengan Real-time Updates -->
                @if($pendingOrdersCount > 0)
                <div class="bg-white border border-orange-200 rounded-2xl p-6 mb-6 animate-fade-in"
                     wire:poll.30s="loadPendingOrders"
                     wire:poll.keep-alive>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            ⏳ Pending Orders 
                            <span class="inline-flex items-center">
                                <span class="ml-1 px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-sm">
                                    {{ $pendingOrdersCount }}
                                </span>
                                <div class="ml-1 w-2 h-2 bg-green-500 rounded-full animate-pulse" 
                                     x-show="ordersUpdated" 
                                     x-transition></div>
                            </span>
                        </h3>
                        <div class="flex items-center space-x-2">
                            <button 
                                wire:click="refreshPendingOrders"
                                wire:loading.attr="disabled"
                                class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50"
                            >
                                @if($refreshingOrders)
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                    Refreshing...
                                @else
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    Refresh Status
                                @endif
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($pendingOrders as $order)
                            @php
                                $orderSummary = $this->getOrderSummary($order);
                                $isFilled = isset($order['order_status']) && strtoupper($order['order_status']) === 'FILLED';
                                $isExpired = $orderSummary['is_expired'];
                            @endphp
                            
                            @if(!$isFilled)
                            <div class="border border-orange-100 rounded-xl p-4 bg-orange-50 hover:bg-orange-100 transition-colors"
                                 :class="{ 'border-green-200 bg-green-50': orderStatus.{{ $order['id'] }} === 'FILLED' }"
                                 x-data="{
                                     orderId: {{ $order['id'] }},
                                     status: '{{ $order['status'] }}',
                                     get isUpdated() {
                                         return this.orderId in window.orderUpdates;
                                     }
                                 }">
                                <!-- ... (keep existing order content) ... -->
                            </div>
                            @endif
                        @endforeach
                    </div>
                    
                    <div class="mt-4 text-center text-sm text-gray-500 flex items-center justify-center space-x-2">
                        <i class="fas fa-info-circle"></i>
                        <span>Orders will auto-cancel in 15 minutes if not filled</span>
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded">Auto-refresh: 30s</span>
                    </div>
                </div>
                @else
                <!-- No Pending Orders Placeholder -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-check-circle text-3xl mb-3 text-green-500 opacity-80"></i>
                        <p class="font-semibold text-lg">No Pending Orders</p>
                        <p class="text-sm mt-1">All orders are filled or cancelled. New limit orders will appear here.</p>
                        <button 
                            wire:click="refreshPendingOrders"
                            class="mt-3 inline-flex items-center text-blue-500 hover:text-blue-700 text-sm font-semibold"
                        >
                            <i class="fas fa-sync-alt mr-1"></i> Check for new orders
                        </button>
                    </div>
                </div>
                @endif

                <!-- Trading Positions dari Binance dengan Real-time -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6"
                     wire:poll.45s="loadCachedPositionsFirst"
                     wire:poll.keep-alive>
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                📊 {{ $isTestnet ? 'Testnet' : 'Live' }} Trading Positions
                                @if($activePositionsCount > 0)
                                <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                    {{ $activePositionsCount }} Active
                                </span>
                                @endif
                                @if($fromCache)
                                <span class="ml-1 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                    <i class="fas fa-database mr-1"></i>
                                    Cached
                                </span>
                                @endif
                            </h3>
                            @if($totalUnrealizedPnl != 0)
                            <p class="text-sm {{ $totalUnrealizedPnl >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1 flex items-center">
                                Total Unrealized P&L: 
                                {{ $totalUnrealizedPnl >= 0 ? '+' : '' }}${{ number_format($totalUnrealizedPnl, 2) }}
                                <div class="ml-1 w-2 h-2 rounded-full animate-pulse" 
                                     :class="positionPnlChanged ? 'bg-yellow-500' : 'bg-transparent'"></div>
                            </p>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <div class="text-xs text-gray-500">
                                Updated: 
                                <span x-text="new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                            </div>
                            <button 
                                wire:click="refreshPositions"
                                wire:loading.attr="disabled"
                                class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50"
                            >
                                @if($loadingPositions)
                                    <i class="fas fa-spinner fa-spin"></i>
                                @else
                                    <i class="fas fa-sync-alt"></i>
                                @endif
                                <span>Refresh</span>
                            </button>
                        </div>
                    </div>

                    @if($activePositionsCount > 0)
                        <!-- Positions Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Symbol
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Side
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Entry/Mark Price
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Unrealized P&L
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Leverage
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($binancePositions as $index => $position)
                                    <tr class="hover:bg-gray-50 transition-colors"
                                        :class="{ 'bg-yellow-50': positionChanged({{ $index }}) }">
                                        <!-- ... (keep existing position row content) ... -->
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Positions Summary -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-sm text-gray-600">Total Positions</div>
                                <div class="text-lg font-bold">{{ $activePositionsCount }}</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-sm text-gray-600">Margin Type</div>
                                <div class="text-lg font-bold">
                                    {{ count(array_unique(array_column($binancePositions, 'margin_type'))) > 1 ? 'Mixed' : ($binancePositions[0]['margin_type'] ?? 'N/A') }}
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-sm text-gray-600">Total Exposure</div>
                                <div class="text-lg font-bold">
                                    @php
                                        $totalExposure = 0;
                                        foreach ($binancePositions as $position) {
                                            $totalExposure += $position['entry_price'] * $position['quantity'];
                                        }
                                    @endphp
                                    ${{ number_format($totalExposure, 2) }}
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-chart-line text-4xl mb-4 opacity-50"></i>
                            <p class="font-semibold text-lg">No Active Trading Positions</p>
                            <p class="text-sm mt-1">
                                @if($isTestnet)
                                    When AI executes test trades and orders are filled, positions will appear here
                                @else
                                    When AI executes real trades and orders are filled, positions will appear here
                                @endif
                            </p>
                            <div class="mt-4 flex justify-center space-x-3">
                                <button 
                                    wire:click="refreshPendingOrders"
                                    class="inline-flex items-center text-blue-500 hover:text-blue-700 text-sm font-semibold"
                                >
                                    <i class="fas fa-sync-alt mr-1"></i> Check Filled Orders
                                </button>
                                <button 
                                    wire:click="refreshPositions"
                                    class="inline-flex items-center text-green-500 hover:text-green-700 text-sm font-semibold"
                                >
                                    <i class="fas fa-redo mr-1"></i> Refresh Positions
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Order Cancel Confirmation Modal -->
                @if($showCancelConfirm && $orderToCancel)
                <!-- ... (keep existing modal) ... -->
                @endif

                <!-- Position Close Confirmation Modal -->
                @if($showCloseConfirm && $positionToClose)
                <!-- ... (keep existing modal) ... -->
                @endif

                <!-- Account Management Modal -->
                @if($showAccountManagement)
                <!-- ... (keep existing modal) ... -->
                @endif

                <!-- Real Trading Stats Grid dengan Animations -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Real Balance Card -->
                    <div class="bg-white border border-green-200 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-shadow"
                         x-data="{ value: @entangle('realBalance'), previousValue: @entangle('realBalance') }"
                         @trading-data-updated.window="previousValue = value; value = $event.detail.balance || value"
                         :class="{ 'border-yellow-400': value !== previousValue }">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-lg font-bold text-gray-900" 
                                     x-text="'$' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(value)">
                                    ${{ number_format($realBalance, 2) }}
                                </div>
                                <div class="text-xs font-semibold text-gray-600">Real Balance</div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-green-500 flex items-center justify-center">
                                <i class="fas fa-wallet text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500" x-show="value !== previousValue">
                            <i class="fas fa-arrow-up text-green-500"></i> Updated
                        </div>
                    </div>

                    <!-- Futures Balance Card -->
                    <div class="bg-white border border-blue-200 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-shadow"
                         x-data="{ value: @entangle('futuresBalance'), previousValue: @entangle('futuresBalance') }"
                         @trading-data-updated.window="previousValue = value; value = $event.detail.balance || value"
                         :class="{ 'border-yellow-400': value !== previousValue }">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-lg font-bold text-gray-900" 
                                     x-text="'$' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(value)">
                                    ${{ number_format($futuresBalance, 2) }}
                                </div>
                                <div class="text-xs font-semibold text-gray-600">Futures Balance</div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-blue-500 flex items-center justify-center">
                                <i class="fab fa-binance text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500" x-show="value !== previousValue">
                            <i class="fas fa-arrow-up text-green-500"></i> Updated
                        </div>
                    </div>

                    <!-- Real PnL Card -->
                    <div class="bg-white border border-purple-200 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-shadow"
                         x-data="{ 
                             value: {{ $user->portfolio->real_realized_pnl ?? 0 }}, 
                             previousValue: {{ $user->portfolio->real_realized_pnl ?? 0 }}
                         }"
                         @trading-data-updated.window="previousValue = value; value = $event.detail.realizedPnl || value"
                         :class="{ 'border-yellow-400': value !== previousValue }">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-lg font-bold" 
                                     :class="value >= 0 ? 'text-green-600' : 'text-red-600'"
                                     x-text="'$' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(value)">
                                    ${{ number_format($user->portfolio->real_realized_pnl ?? 0, 2) }}
                                </div>
                                <div class="text-xs font-semibold text-gray-600">Realized P&L</div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-purple-500 flex items-center justify-center">
                                <i class="fas fa-coins text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500" x-show="value !== previousValue">
                            <i class="fas" :class="value > previousValue ? 'fa-arrow-up text-green-500' : 'fa-arrow-down text-red-500'"></i>
                            Updated
                        </div>
                    </div>

                    <!-- Unrealized PnL Card -->
                    <div class="bg-white border border-orange-200 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-shadow"
                         x-data="{ 
                             value: @entangle('totalUnrealizedPnl'), 
                             previousValue: @entangle('totalUnrealizedPnl')
                         }"
                         @trading-data-updated.window="previousValue = value; value = $event.detail.totalUnrealizedPnl || value"
                         :class="{ 'border-yellow-400': value !== previousValue }">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-lg font-bold" 
                                     :class="value >= 0 ? 'text-green-600' : 'text-red-600'"
                                     x-text="(value >= 0 ? '+' : '') + '$' + new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(Math.abs(value))">
                                    {{ $totalUnrealizedPnl >= 0 ? '+' : '' }}${{ number_format(abs($totalUnrealizedPnl), 2) }}
                                </div>
                                <div class="text-xs font-semibold text-gray-600">Unrealized P&L</div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center">
                                <i class="fas fa-chart-line text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500" x-show="value !== previousValue">
                            <i class="fas" :class="value > previousValue ? 'fa-arrow-up text-green-500' : 'fa-arrow-down text-red-500'"></i>
                            Live update
                        </div>
                    </div>
                </div>

                <!-- Cache Stats untuk Debugging -->
                @if(config('app.debug') && !empty($cacheStats))
                <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Cache Statistics</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-gray-600">Hits:</span>
                            <span class="font-semibold ml-1">{{ $cacheStats['hits'] ?? 0 }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Misses:</span>
                            <span class="font-semibold ml-1">{{ $cacheStats['misses'] ?? 0 }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Keys:</span>
                            <span class="font-semibold ml-1">{{ $cacheStats['keys'] ?? 0 }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Memory:</span>
                            <span class="font-semibold ml-1">{{ $cacheStats['memory'] ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        Last cache update: {{ $lastCacheUpdate ?? 'Never' }}
                    </div>
                </div>
                @endif

            </div>
        @endif

    </div>

    <!-- WebSocket/Pusher Integration -->
    <script>
    function tradingDashboard() {
        return {
            // State
            websocketConnected: false,
            autoRefresh: true,
            showUpdateNotification: false,
            updateMessage: '',
            loading: false,
            ordersUpdated: false,
            positionPnlChanged: false,
            lastUpdate: null,
            cacheStatus: {
                cached: false,
                age: 0
            },
            
            // WebSocket/Pusher connection
            init() {
                this.connectWebSocket();
                this.startAutoRefresh();
                this.initializeEventListeners();
                this.updateCacheStatus();
            },
            
            // Connect to WebSocket/Pusher
            connectWebSocket() {
                // Pusher implementation
                if (typeof Pusher !== 'undefined') {
                    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
                        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                        encrypted: true
                    });
                    
                    const channel = pusher.subscribe('trading.{{ $user->id }}');
                    
                    channel.bind('trading-data-updated', (data) => {
                        this.onTradingDataUpdated(data);
                    });
                    
                    channel.bind('order-filled', (data) => {
                        this.showNotification('Order Filled', `${data.symbol} ${data.side} order filled at $${data.price}`);
                        @this.call('loadPendingOrders');
                    });
                    
                    channel.bind('position-updated', (data) => {
                        this.showNotification('Position Updated', `${data.symbol} P&L: $${data.pnl}`);
                        @this.call('loadCachedPositionsFirst');
                    });
                    
                    this.websocketConnected = true;
                }
                
                // Fallback to polling if WebSocket not available
                if (!this.websocketConnected) {
                    console.log('WebSocket not available, using polling');
                }
            },
            
            // Handle trading data updates
            onTradingDataUpdated(data) {
                console.log('📡 Trading data updated:', data);
                
                this.showNotification('Data Updated', 'New trading data received from server');
                this.lastUpdate = new Date();
                this.updateCacheStatus();
                
                // Trigger Livewire updates
                if (data.positions || data.orders) {
                    this.ordersUpdated = true;
                    setTimeout(() => this.ordersUpdated = false, 3000);
                    
                    if (data.positions) {
                        this.positionPnlChanged = true;
                        setTimeout(() => this.positionPnlChanged = false, 3000);
                    }
                }
            },
            
            // Show notification
            showNotification(title, message) {
                this.updateMessage = message;
                this.showUpdateNotification = true;
                
                setTimeout(() => {
                    this.showUpdateNotification = false;
                }, 5000);
            },
            
            // Auto-refresh system
            startAutoRefresh() {
                if (this.autoRefresh) {
                    this.refreshInterval = setInterval(() => {
                        if (@this.binanceConnected && !this.loading) {
                            this.loading = true;
                            
                            // Refresh data with priority
                            @this.call('loadCachedPositionsFirst').then(() => {
                                this.loading = false;
                                this.updateCacheStatus();
                            });
                        }
                    }, 45000); // 45 seconds
                }
            },
            
            // Toggle auto-refresh
            toggleAutoRefresh() {
                this.autoRefresh = !this.autoRefresh;
                
                if (this.autoRefresh) {
                    this.startAutoRefresh();
                } else {
                    clearInterval(this.refreshInterval);
                }
                
                // Save preference to localStorage
                localStorage.setItem('tradingAutoRefresh', this.autoRefresh);
            },
            
            // Initialize event listeners
            initializeEventListeners() {
                // Load auto-refresh preference
                const savedPreference = localStorage.getItem('tradingAutoRefresh');
                if (savedPreference !== null) {
                    this.autoRefresh = savedPreference === 'true';
                }
                
                // Listen for Livewire events
                @this.on('refreshData', () => {
                    this.showNotification('Refreshing', 'Data refresh started...');
                    this.loading = true;
                });
                
                @this.on('dataRefreshed', () => {
                    this.showNotification('Success', 'Data refreshed successfully');
                    this.loading = false;
                    this.updateCacheStatus();
                });
                
                @this.on('cacheUpdated', () => {
                    this.showNotification('Cache Updated', 'Trading cache has been updated');
                    this.updateCacheStatus();
                });
            },
            
            // Update cache status
            updateCacheStatus() {
                this.cacheStatus.cached = @entangle('fromCache');
                this.cacheStatus.age = Math.floor(Math.random() * 30); // Demo - replace with actual
            },
            
            // Check if position data changed
            positionChanged(index) {
                return window.positionUpdates && window.positionUpdates[index];
            }
        };
    }
    
    // Global variables for tracking updates
    window.orderUpdates = {};
    window.positionUpdates = {};
    window.lastPositions = {};
    
    // Initialize when Livewire is loaded
    document.addEventListener('livewire:initialized', () => {
        @this.on('binance-connected', () => {
            console.log('🔧 Binance connected event received');
            
            // Show success notification
            setTimeout(() => {
                @this.call('refreshData');
            }, 1000);
        });
        
        @this.on('cache-refreshed', () => {
            const event = new CustomEvent('cache-refreshed');
            document.dispatchEvent(event);
        });
    });
    </script>

    <!-- CSS Animations -->
    <style>
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { 
            opacity: 0;
            transform: translateY(10px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    .animate-slide-up {
        animation: slideUp 0.5s ease-out;
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    /* Smooth transitions for updated values */
    .value-updated {
        animation: pulse 1s ease-in-out;
    }
    </style>
</div>