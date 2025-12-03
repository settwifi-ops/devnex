<!-- resources/views/livewire/real-trading-page.blade.php -->
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Real Trading</h1>
            <p class="text-gray-600 mt-2">Live trading with your Binance account</p>
            
            @if(config('app.debug') && $fromCache)
                <div class="inline-flex items-center mt-2 px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                    <i class="fas fa-database mr-2"></i>
                    <span>Data from cache</span>
                    @if($lastCacheUpdate)
                        <span class="ml-2 text-xs">(Updated: {{ $lastCacheUpdate }})</span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl mb-6">
                {{ session('info') }}
            </div>
        @endif

        <!-- Conditional Content -->
        @if(!$hasRealSubscription)
            <!-- UPGRADE CARD -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 mb-6">
                <div class="text-center">
                    <div class="inline-block bg-orange-100 text-orange-800 px-4 py-1 rounded-full text-sm font-semibold mb-4">
                        PREMIUM ADD-ON
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Upgrade to Real Trading</h2>
                    <p class="text-gray-600 mb-6">Already enjoying virtual trading? Take it live with real Binance integration.</p>
                    
                    <div class="bg-gray-50 rounded-xl p-6 mb-6">
                        <div class="text-4xl font-bold text-gray-900 mb-2">$29.99<span class="text-lg text-gray-600">/month</span></div>
                        <ul class="text-left space-y-3 max-w-md mx-auto">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span>Real Binance API Integration</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span>Live Money Trading</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span>Same AI Signals, Real Execution</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span>Advanced Risk Management</span>
                            </li>
                        </ul>
                    </div>

                    <button 
                        wire:click="upgradeToRealTrading"
                        wire:loading.attr="disabled"
                        class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        @if($upgrading)
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Processing...
                        @else
                            Upgrade to Real Trading - $29.99/month
                        @endif
                    </button>
                </div>
            </div>

        @elseif(!$binanceConnected)
            <!-- API CONNECTION CARD -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
                <div class="text-center mb-6">
                    <div class="inline-block bg-green-100 text-green-800 px-4 py-1 rounded-full text-sm font-semibold mb-4">
                        REAL TRADING ACTIVE
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Connect Your Binance Account</h2>
                    <p class="text-gray-600">Secure connection to start live trading</p>
                </div>

                <!-- Environment Selection -->
                <div class="flex space-x-4 mb-6">
                    <button 
                        wire:click="switchConnectionType('testnet')"
                        class="flex-1 py-3 px-4 rounded-xl border-2 font-semibold transition-all duration-200
                            {{ $connectionType === 'testnet' 
                                ? 'bg-yellow-50 border-yellow-400 text-yellow-800 shadow-md' 
                                : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}"
                    >
                        <div class="flex items-center justify-center space-x-2">
                            <i class="fas fa-flask"></i>
                            <span>Testnet Mode</span>
                        </div>
                        <div class="text-xs mt-1 opacity-75">Fake Money • Safe Testing</div>
                    </button>
                    
                    <button 
                        wire:click="switchConnectionType('mainnet')"
                        class="flex-1 py-3 px-4 rounded-xl border-2 font-semibold transition-all duration-200
                            {{ $connectionType === 'mainnet' 
                                ? 'bg-red-50 border-red-400 text-red-800 shadow-md' 
                                : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}"
                    >
                        <div class="flex items-center justify-center space-x-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Live Trading</span>
                        </div>
                        <div class="text-xs mt-1 opacity-75">Real Money • High Risk</div>
                    </button>
                </div>

                <!-- Connection Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-info text-white text-xs"></i>
                        </div>
                        <div class="text-sm text-blue-800">
                            @if($connectionType === 'testnet')
                                <strong>Testnet Instructions:</strong><br>
                                1. Visit <a href="https://testnet.binancefuture.com" target="_blank" class="underline">testnet.binancefuture.com</a><br>
                                2. Register/login with email<br>
                                3. Go to API Management and create new key<br>
                                4. Enable Futures Trading permissions<br>
                                5. Copy API Key & Secret here
                            @else
                                <strong>Live Trading Instructions:</strong><br>
                                1. Visit <a href="https://www.binance.com" target="_blank" class="underline">binance.com</a><br>
                                2. Go to API Management in your account<br>
                                3. Create new API Key with Futures Trading enabled<br>
                                4. <strong class="text-red-600">DISABLE Withdrawals</strong> for security<br>
                                5. Copy API Key & Secret here
                            @endif
                        </div>
                    </div>
                </div>

                <form wire:submit="connectBinance" class="max-w-md mx-auto">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                API Key 
                                <span class="text-xs text-gray-500">
                                    (from {{ $connectionType === 'testnet' ? 'testnet.binancefuture.com' : 'binance.com' }})
                                </span>
                            </label>
                            <input 
                                type="text" 
                                wire:model="api_key"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter your Binance API Key"
                                required
                            >
                            @error('api_key') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">API Secret</label>
                            <input 
                                type="password" 
                                wire:model="api_secret"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter your Binance API Secret" 
                                required
                            >
                            @error('api_secret') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed
                                {{ $connectionType === 'testnet' 
                                    ? 'bg-yellow-500 hover:bg-yellow-600 text-white' 
                                    : 'bg-red-500 hover:bg-red-600 text-white' }}"
                        >
                            @if($connecting)
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Connecting...
                            @else
                                <i class="fab fa-binance mr-2"></i>
                                Connect {{ $connectionType === 'testnet' ? 'Testnet' : 'Live' }} Account
                            @endif
                        </button>
                    </div>
                </form>

                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                    <p class="text-yellow-800 text-sm">
                        <strong>Security Note:</strong> 
                        @if($connectionType === 'testnet')
                            Testnet API keys are safe to use - no real funds involved.
                        @else
                            For live trading, always <strong>disable withdrawals</strong> in your API key permissions.
                        @endif
                    </p>
                </div>
            </div>

        @else
            <!-- REAL TRADING DASHBOARD -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                
                <!-- Header dengan Account Management -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center space-x-2 bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span>BINANCE CONNECTED • {{ $isTestnet ? 'TESTNET' : 'LIVE TRADING' }}</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Real Trading Dashboard</h2>
                    <p class="text-gray-600">
                        {{ $isTestnet ? 'Testing with fake money' : 'Live trading with real money' }}
                    </p>
                    
                    <!-- Account Management Button -->
                    <button 
                        wire:click="toggleAccountManagement"
                        class="mt-4 inline-flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-xl transition-colors"
                    >
                        <i class="fas fa-cog"></i>
                        <span>Manage API Keys</span>
                    </button>
                </div>

                <!-- Account Management Modal -->
                @if($showAccountManagement)
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Manage API Keys</h3>
                                <button 
                                    wire:click="toggleAccountManagement"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <!-- Connected Accounts -->
                            <div class="mb-6">
                                <h4 class="font-semibold text-gray-700 mb-3">Connected Accounts</h4>
                                
                                @if(count($userAccounts) > 0)
                                    <div class="space-y-3">
                                        @foreach($userAccounts as $account)
                                        <div class="border border-gray-200 rounded-xl p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="font-semibold text-gray-900">
                                                        {{ $account->is_testnet ? 'Testnet Account' : 'Live Trading Account' }}
                                                    </div>
                                                    <div class="text-sm text-gray-600">
                                                        Connected: {{ $account->created_at ? $account->created_at->format('M d, Y') : 'N/A' }}
                                                    </div>
                                                    @if($account->balance_snapshot)
                                                    <div class="text-sm text-green-600 font-semibold">
                                                        Balance: ${{ number_format($account->balance_snapshot, 2) }}
                                                    </div>
                                                    @endif
                                                </div>
                                                <button 
                                                    wire:click="deleteAccount({{ $account->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-lg text-sm font-semibold transition-colors disabled:opacity-50"
                                                >
                                                    @if($deleting)
                                                        <i class="fas fa-spinner fa-spin"></i>
                                                    @else
                                                        <i class="fas fa-trash"></i>
                                                    @endif
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-gray-500">
                                        <i class="fas fa-key text-2xl mb-2 opacity-50"></i>
                                        <p>No connected accounts</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Add New Account -->
                            <div class="border-t pt-4">
                                <h4 class="font-semibold text-gray-700 mb-3">Add New Account</h4>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <button 
                                        wire:click="switchToTestnet"
                                        class="flex items-center justify-center space-x-2 bg-yellow-500 hover:bg-yellow-600 text-white py-3 px-4 rounded-xl font-semibold transition-colors"
                                    >
                                        <i class="fas fa-flask"></i>
                                        <span>Testnet</span>
                                    </button>
                                    
                                    <button 
                                        wire:click="switchToMainnet"
                                        class="flex items-center justify-center space-x-2 bg-red-500 hover:bg-red-600 text-white py-3 px-4 rounded-xl font-semibold transition-colors"
                                    >
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span>Live Trading</span>
                                    </button>
                                </div>
                                
                                <p class="text-sm text-gray-600 mt-3 text-center">
                                    Switch mode to add new API keys
                                </p>
                            </div>

                            <!-- Danger Zone -->
                            <div class="border-t pt-4 mt-4">
                                <h4 class="font-semibold text-red-700 mb-3">Danger Zone</h4>
                                
                                <div class="space-y-2">
                                    <button 
                                        wire:click="deleteAccount(null, {{ $isTestnet ? 'true' : 'false' }})"
                                        wire:loading.attr="disabled"
                                        class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-xl font-semibold transition-colors disabled:opacity-50 text-sm"
                                    >
                                        @if($deleting)
                                            <i class="fas fa-spinner fa-spin mr-2"></i>
                                            Deleting...
                                        @else
                                            <i class="fas fa-trash mr-2"></i>
                                            Delete All {{ $isTestnet ? 'Testnet' : 'Live' }} Accounts
                                        @endif
                                    </button>
                                    
                                    <button 
                                        wire:click="deleteAccount(null, null)"
                                        wire:loading.attr="disabled"
                                        class="w-full bg-red-700 hover:bg-red-800 text-white py-2 px-4 rounded-xl font-semibold transition-colors disabled:opacity-50 text-sm"
                                    >
                                        @if($deleting)
                                            <i class="fas fa-spinner fa-spin mr-2"></i>
                                            Deleting...
                                        @else
                                            <i class="fas fa-nuclear mr-2"></i>
                                            Delete ALL Accounts (Both Testnet & Live)
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Environment Warning -->
                <div class="mb-6 p-4 rounded-xl border 
                    @if($isTestnet) 
                        bg-yellow-50 border-yellow-200 
                    @else 
                        bg-red-50 border-red-200 
                    @endif">
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
                        
                        <div class="text-right">
                            <div class="text-lg font-bold">${{ number_format($futuresBalance, 2) }}</div>
                            <div class="text-sm {{ $futuresBalance >= $minBalanceRequired ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                @if($futuresBalance >= $minBalanceRequired)
                                    ✅ Sufficient balance
                                @else
                                    ⚠️ Min: ${{ $minBalanceRequired }} required
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cache Indicator -->
                    @if($fromCache)
                    <div class="mt-3 pt-3 border-t border-yellow-300">
                        <div class="flex items-center text-xs text-yellow-700">
                            <i class="fas fa-database mr-2"></i>
                            <span>Showing cached data (auto-refreshes every 30s)</span>
                            <button 
                                wire:click="loadBinancePositions"
                                class="ml-auto text-yellow-600 hover:text-yellow-800 text-xs font-semibold"
                            >
                                <i class="fas fa-sync-alt mr-1"></i>
                                Refresh Now
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Trading Control Card -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-lg font-semibold text-gray-900">Trading Control</h3>
                            <p class="text-gray-600 text-sm">Enable/disable {{ $isTestnet ? 'testnet' : 'real money' }} trading</p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <!-- Balance Display -->
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-900">${{ number_format($futuresBalance, 2) }}</div>
                                <div class="text-sm {{ $futuresBalance >= $minBalanceRequired ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                    @if($futuresBalance >= $minBalanceRequired)
                                        ✅ Sufficient balance
                                    @else
                                        ⚠️ Min: ${{ $minBalanceRequired }}
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

                    <!-- Status Badge -->
                    <div class="mt-4 flex justify-center">
                        @if($realTradingEnabled)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-play-circle mr-2"></i>
                                TRADING ACTIVE - AI is executing {{ $isTestnet ? 'test' : 'real' }} trades
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

                <!-- Action Buttons with Cache Controls -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
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
                        @if($loading)
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            <span>Refreshing...</span>
                        @else
                            <i class="fas fa-redo mr-2"></i>
                            <span>Refresh All</span>
                        @endif
                    </button>
                    
                    <button 
                        wire:click="forceCacheRefresh"
                        wire:loading.attr="disabled"
                        class="flex items-center justify-center space-x-2 bg-yellow-500 hover:bg-yellow-600 text-white py-3 px-4 rounded-xl font-semibold transition-colors disabled:opacity-50"
                        title="Force refresh cache data"
                    >
                        @if($loading)
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            <span>Clearing...</span>
                        @else
                            <i class="fas fa-broom mr-2"></i>
                            <span>Clear Cache</span>
                        @endif
                    </button>
                </div>

                <!-- Pending Orders Section -->
                @if($pendingOrdersCount > 0)
                <div class="bg-white border border-orange-200 rounded-2xl p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            ⏳ Pending Orders ({{ $pendingOrdersCount }})
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
                            @endphp
                            
                            @if(isset($order['status']) && !in_array(strtoupper($order['status']), ['FILLED', 'CANCELLED']))
                            <div class="border border-orange-100 rounded-xl p-4 bg-orange-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <span class="font-semibold text-gray-900 text-lg">{{ $order['symbol'] ?? 'N/A' }}</span>
                                            @if(isset($order['side']))
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $order['side'] === 'BUY' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $order['side'] }}
                                            </span>
                                            @endif
                                            @if(isset($order['position_type']))
                                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                {{ $order['position_type'] }}
                                            </span>
                                            @endif
                                            
                                            <!-- BINANCE STATUS BADGE -->
                                            @if(isset($order['order_status']) && $order['order_status'])
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ strtoupper($order['order_status']) === 'FILLED' ? 'bg-green-100 text-green-800' : 
                                                   (strtoupper($order['order_status']) === 'PARTIALLY_FILLED' ? 'bg-yellow-100 text-yellow-800' : 
                                                   (strtoupper($order['order_status']) === 'CANCELLED' ? 'bg-red-100 text-red-800' : 
                                                   (strtoupper($order['order_status']) === 'NEW' ? 'bg-blue-100 text-blue-800' : 
                                                   'bg-gray-100 text-gray-800'))) }}">
                                                Binance: {{ $order['order_status'] }}
                                            </span>
                                            @endif
                                            
                                            <!-- LOCAL STATUS BADGE -->
                                            @if(isset($order['status']))
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ strtoupper($order['status']) === 'PENDING' ? 'bg-orange-100 text-orange-800' : 
                                                   (strtoupper($order['status']) === 'PARTIALLY_FILLED' ? 'bg-yellow-100 text-yellow-800' : 
                                                   'bg-gray-100 text-gray-800') }}">
                                                Local: {{ $order['status'] }}
                                            </span>
                                            @endif
                                        </div>
                                        
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            @if(isset($order['limit_price']))
                                            <div>
                                                <span class="text-gray-600">Limit Price:</span>
                                                <span class="font-semibold ml-2">${{ number_format($order['limit_price'], 4) }}</span>
                                            </div>
                                            @endif
                                            @if(isset($order['quantity']))
                                            <div>
                                                <span class="text-gray-600">Quantity:</span>
                                                <span class="font-semibold ml-2">{{ number_format($order['quantity'], 6) }}</span>
                                            </div>
                                            @endif
                                            @if(isset($order['limit_price']) && isset($order['quantity']))
                                            <div>
                                                <span class="text-gray-600">Total Value:</span>
                                                <span class="font-semibold ml-2">${{ number_format($order['limit_price'] * $order['quantity'], 2) }}</span>
                                            </div>
                                            @endif
                                            <div>
                                                <span class="text-gray-600">Expires:</span>
                                                <span class="font-semibold {{ $orderSummary['is_expired'] ?? false ? 'text-red-600' : 'text-orange-600' }}">
                                                    {{ $orderSummary['time_left'] ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2 flex items-center space-x-4 text-sm">
                                            @if(isset($order['binance_order_id']))
                                            <div class="flex items-center space-x-2">
                                                <span class="text-gray-600">Order ID:</span>
                                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                                    {{ $order['binance_order_id'] }}
                                                </span>
                                            </div>
                                            @endif
                                            @if(isset($order['created_at']))
                                            <div class="flex items-center space-x-2">
                                                <span class="text-gray-600">Placed:</span>
                                                <span class="text-gray-500">
                                                    {{ \Carbon\Carbon::parse($order['created_at'])->format('H:i:s') }}
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                        
                                        @if(isset($order['notes']) && $order['notes'])
                                        <div class="mt-2 p-2 bg-gray-50 rounded text-xs text-gray-600">
                                            <i class="fas fa-sticky-note mr-1"></i>
                                            {{ $order['notes'] }}
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex flex-col space-y-2 ml-4">
                                        <!-- Check Status Button -->
                                        <button 
                                            wire:click="checkOrderStatus({{ $order['id'] ?? 0 }})"
                                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg text-sm font-semibold transition-colors"
                                            title="Check current status on Binance"
                                        >
                                            <i class="fas fa-search mr-1"></i> Check
                                        </button>
                                        
                                        <!-- Cancel Button -->
                                        <button 
                                            wire:click="confirmCancelOrder({{ $order['id'] ?? 0 }})"
                                            wire:loading.attr="disabled"
                                            class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg text-sm font-semibold transition-colors disabled:opacity-50"
                                            {{ ($orderSummary['is_expired'] ?? false) ? 'disabled' : '' }}
                                        >
                                            @if($cancellingOrderId == ($order['id'] ?? null))
                                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                                Cancelling...
                                            @else
                                                <i class="fas fa-times mr-1"></i> Cancel
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    
                    <div class="mt-4 text-center text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Orders will auto-cancel in 15 minutes if not filled • Click "Refresh Status" to update
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

                <!-- Trading Positions dari Binance -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                📊 {{ $isTestnet ? 'Testnet' : 'Live' }} Trading Positions
                                @if($activePositionsCount > 0)
                                <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                    {{ $activePositionsCount }} Active
                                </span>
                                @endif
                            </h3>
                            @if($totalUnrealizedPnl != 0)
                            <p class="text-sm {{ $totalUnrealizedPnl >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                                Total Unrealized P&L: 
                                {{ $totalUnrealizedPnl >= 0 ? '+' : '' }}${{ number_format($totalUnrealizedPnl, 2) }}
                            </p>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-2">
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
                                    @if(isset($position['symbol']) && isset($position['quantity']) && $position['quantity'] > 0)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="font-semibold text-gray-900">{{ $position['symbol'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $position['position_type'] ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if(isset($position['side']))
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $position['side'] === 'BUY' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $position['side'] }}
                                            </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm">
                                                <div class="text-gray-600">Entry: ${{ number_format($position['entry_price'] ?? 0, 4) }}</div>
                                                <div class="text-gray-900">Mark: ${{ number_format($position['mark_price'] ?? 0, 4) }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($position['quantity'] ?? 0, 6) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @php
                                                $pnlFormatted = $this->formatPnl($position['unrealized_pnl'] ?? 0);
                                                $pnlPercentageFormatted = number_format(abs($position['pnl_percentage'] ?? 0), 2) . '%';
                                            @endphp
                                            <div class="text-sm font-semibold {{ $pnlFormatted['color'] ?? 'text-gray-600' }}">
                                                {{ $pnlFormatted['formatted'] ?? '$0.00' }}
                                            </div>
                                            <div class="text-xs {{ $pnlFormatted['color'] ?? 'text-gray-600' }}">
                                                {{ $pnlFormatted['sign'] ?? '' }}{{ $pnlPercentageFormatted }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if(isset($position['leverage']))
                                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                {{ $position['leverage'] }}x
                                            </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                            <button 
                                                wire:click="confirmClosePosition({{ $index }})"
                                                wire:loading.attr="disabled"
                                                class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-lg text-xs font-semibold transition-colors disabled:opacity-50"
                                                {{ $closingPositionId === $position['symbol'] ? 'disabled' : '' }}
                                            >
                                                @if($closingPositionId === $position['symbol'])
                                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                                    Closing...
                                                @else
                                                    <i class="fas fa-times mr-1"></i>
                                                    Close
                                                @endif
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
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
                                    @php
                                        $marginTypes = array_unique(array_column($binancePositions, 'margin_type'));
                                        $marginTypes = array_filter($marginTypes);
                                    @endphp
                                    @if(count($marginTypes) > 1)
                                        Mixed
                                    @elseif(count($marginTypes) == 1)
                                        {{ $marginTypes[0] }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-sm text-gray-600">Total Exposure</div>
                                <div class="text-lg font-bold">
                                    @php
                                        $totalExposure = 0;
                                        foreach ($binancePositions as $position) {
                                            if (isset($position['entry_price']) && isset($position['quantity'])) {
                                                $totalExposure += $position['entry_price'] * $position['quantity'];
                                            }
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
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Confirm Cancellation</h3>
                                <button 
                                    wire:click="closeCancelConfirm"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div class="mb-6">
                                <p class="text-gray-600 mb-3">Are you sure you want to cancel this order?</p>
                                
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                                        <div>
                                            <div class="font-semibold">{{ $orderToCancel->symbol }}</div>
                                            <div class="text-sm">
                                                {{ $orderToCancel->side }} • {{ $orderToCancel->position_type }}
                                            </div>
                                            <div class="text-sm mt-1">
                                                Price: ${{ number_format($orderToCancel->limit_price, 4) }} • 
                                                Qty: {{ number_format($orderToCancel->quantity, 6) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button 
                                    wire:click="closeCancelConfirm"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button 
                                    wire:click="cancelPendingOrder"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold transition-colors disabled:opacity-50"
                                >
                                    @if($cancellingOrderId)
                                        <i class="fas fa-spinner fa-spin mr-1"></i>
                                        Processing...
                                    @else
                                        Yes, Cancel Order
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Position Close Confirmation Modal -->
                @if($showCloseConfirm && $positionToClose)
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Close Position</h3>
                                <button 
                                    wire:click="closeCloseConfirm"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div class="mb-6">
                                <p class="text-gray-600 mb-3">Are you sure you want to close this position?</p>
                                
                                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                    <div class="space-y-2">
                                        @if(isset($positionToClose['symbol']))
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Symbol:</span>
                                            <span class="font-semibold">{{ $positionToClose['symbol'] }}</span>
                                        </div>
                                        @endif
                                        @if(isset($positionToClose['side']))
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Side:</span>
                                            <span class="font-semibold {{ $positionToClose['side'] === 'BUY' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $positionToClose['side'] }} ({{ $positionToClose['position_type'] ?? 'N/A' }})
                                            </span>
                                        </div>
                                        @endif
                                        @if(isset($positionToClose['quantity']))
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Quantity:</span>
                                            <span class="font-semibold">{{ number_format($positionToClose['quantity'], 6) }}</span>
                                        </div>
                                        @endif
                                        @if(isset($positionToClose['entry_price']))
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Entry Price:</span>
                                            <span class="font-semibold">${{ number_format($positionToClose['entry_price'], 4) }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Current P&L:</span>
                                            @php
                                                $pnl = $positionToClose['unrealized_pnl'] ?? 0;
                                                $pnlFormatted = $this->formatPnl($pnl);
                                            @endphp
                                            <span class="font-semibold {{ $pnlFormatted['color'] }}">
                                                {{ $pnlFormatted['formatted'] }} 
                                                @if(isset($positionToClose['pnl_percentage']))
                                                ({{ number_format(abs($positionToClose['pnl_percentage']), 2) }}%)
                                                @endif
                                            </span>
                                        </div>
                                        @if(isset($positionToClose['leverage']))
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Leverage:</span>
                                            <span class="font-semibold">{{ $positionToClose['leverage'] }}x</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        This will place a MARKET order with <strong>reduceOnly=true</strong> to close the position.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button 
                                    wire:click="closeCloseConfirm"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button 
                                    wire:click="closePositionConfirmed"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold transition-colors disabled:opacity-50"
                                >
                                    @if($closingPositionId === ($positionToClose['symbol'] ?? null))
                                        <i class="fas fa-spinner fa-spin mr-1"></i>
                                        Closing...
                                    @else
                                        Yes, Close Position
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Real Trading Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Real Balance Card -->
                    <div class="bg-white border border-green-200 rounded-2xl p-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-lg font-bold text-gray-900">${{ number_format($realBalance, 2) }}</div>
                                <div class="text-xs font-semibold text-gray-600">Real Balance</div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-green-500 flex items-center justify-center">
                                <i class="fas fa-wallet text-white text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Futures Balance Card -->
                    <div class="bg-white border border-blue-200 rounded-2xl p-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-lg font-bold text-gray-900">
                                    ${{ number_format($futuresBalance, 2) }}
                                </div>
                                <div class="text-xs font-semibold text-gray-600">Futures Balance</div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-blue-500 flex items-center justify-center">
                                <i class="fab fa-binance text-white text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Real PnL Card -->
                    <div class="bg-white border border-purple-200 rounded-2xl p-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                @php
                                    $realizedPnl = $user->portfolio->real_realized_pnl ?? 0;
                                @endphp
                                <div class="text-lg font-bold {{ $realizedPnl >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format($realizedPnl, 2) }}
                                </div>
                                <div class="text-xs font-semibold text-gray-600">Realized P&L</div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-purple-500 flex items-center justify-center">
                                <i class="fas fa-coins text-white text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Unrealized PnL Card -->
                    <div class="bg-white border border-orange-200 rounded-2xl p-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-lg font-bold {{ $totalUnrealizedPnl >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $totalUnrealizedPnl >= 0 ? '+' : '' }}${{ number_format($totalUnrealizedPnl, 2) }}
                                </div>
                                <div class="text-xs font-semibold text-gray-600">Unrealized P&L</div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center">
                                <i class="fas fa-chart-line text-white text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cache Stats (Debug Mode) -->
                @if(config('app.debug') && !empty($cacheStats))
                <div class="mt-6 p-4 bg-gray-100 rounded-xl">
                    <h4 class="font-semibold text-gray-700 mb-2">Cache Statistics</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                        <div class="bg-white p-2 rounded">
                            <div class="text-gray-600">Hits</div>
                            <div class="font-bold">{{ $cacheStats['hits'] ?? 0 }}</div>
                        </div>
                        <div class="bg-white p-2 rounded">
                            <div class="text-gray-600">Misses</div>
                            <div class="font-bold">{{ $cacheStats['misses'] ?? 0 }}</div>
                        </div>
                        <div class="bg-white p-2 rounded">
                            <div class="text-gray-600">Keys</div>
                            <div class="font-bold">{{ $cacheStats['keys'] ?? 0 }}</div>
                        </div>
                        <div class="bg-white p-2 rounded">
                            <div class="text-gray-600">Memory</div>
                            <div class="font-bold">{{ $cacheStats['memory'] ?? '0 MB' }}</div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        @endif

    </div>

    <!-- JavaScript untuk auto-refresh dan events -->
    @script
    <script>
        $wire.on('binance-connected', () => {
            console.log('🔧 Binance connected event received');
            
            // Show success toast
            setTimeout(() => {
                $wire.refreshData();
            }, 1000);
        });
        
        // Auto-refresh positions setiap 30 detik jika Binance connected
        setInterval(() => {
            if (@this.binanceConnected && @this.realTradingEnabled) {
                $wire.loadBinancePositions();
            }
        }, 30000);
    </script>
    @endscript
</div>