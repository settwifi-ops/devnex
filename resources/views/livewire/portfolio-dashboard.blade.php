<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30">
    <div class="container mx-auto px-4 py-6">
        <!-- Enhanced Header Section -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
            <div class="flex-1">
                <div class="flex items-center mb-3">
                    <div class="relative group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-4 shadow-xl group-hover:shadow-2xl transition-all duration-500 group-hover:scale-110">
                            <i class="fa-solid fa-chart-pie text-white text-lg"></i>
                        </div>
                        <!-- Animated rings -->
                        <div class="absolute -inset-2 border-2 border-blue-200/30 rounded-2xl animate-pulse group-hover:animate-none"></div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 bg-gradient-to-r from-gray-900 to-blue-900 bg-clip-text text-transparent">
                            Virtual Portfolio
                        </h1>
                        <p class="text-gray-600 text-sm mt-1 flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                            AI-powered trading portfolio with real-time analytics
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
                <!-- Last Updated -->
                <div class="text-sm text-gray-700 bg-white/80 backdrop-blur-sm px-4 py-2.5 rounded-xl border border-gray-200/60 font-medium shadow-lg hover:shadow-xl transition-all duration-300">
                    <i class="fas fa-clock mr-2 text-blue-500"></i>
                    Updated: <span class="font-mono text-gray-900">{{ $lastUpdate }}</span>
                </div>
                
                <button wire:click="refreshPortfolio" 
                        wire:loading.attr="disabled"
                        class="bg-gradient-to-br from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-2.5 px-5 rounded-xl flex items-center text-sm transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 active:translate-y-0 group disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-sync-alt mr-2 text-sm group-hover:rotate-180 transition-transform duration-500"></i>
                    <span wire:loading.remove>Refresh</span>
                    <span wire:loading>Refreshing...</span>
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('message'))
            <div class="mb-6 bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl text-sm flex items-center shadow-xl hover:shadow-2xl transition-all duration-500 backdrop-blur-sm" role="alert">
                <div class="w-5 h-5 rounded-full bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center mr-4 flex-shrink-0 shadow-lg">
                    <i class="fas fa-check text-white text-xs"></i>
                </div>
                <div class="flex-1">
                    <span class="font-bold">Success!</span> {{ session('message') }}
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 ml-4 transition-colors duration-200">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-gradient-to-r from-rose-50 to-red-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl text-sm flex items-center shadow-xl hover:shadow-2xl transition-all duration-500 backdrop-blur-sm" role="alert">
                <div class="w-5 h-5 rounded-full bg-gradient-to-br from-rose-500 to-red-500 flex items-center justify-center mr-4 flex-shrink-0 shadow-lg">
                    <i class="fas fa-exclamation text-white text-xs"></i>
                </div>
                <div class="flex-1">
                    <span class="font-bold">Error!</span> {{ session('error') }}
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 ml-4 transition-colors duration-200">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        @endif

        <!-- Loading State -->
        @if($isLoading)
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-12 mb-8">
            <div class="flex flex-col items-center justify-center space-y-6">
                <div class="relative">
                    <div class="w-16 h-16 border-4 border-blue-200 rounded-full"></div>
                    <div class="w-16 h-16 border-4 border-blue-600 rounded-full animate-spin absolute top-0 left-0 border-t-transparent"></div>
                </div>
                <div class="text-center space-y-2">
                    <p class="text-xl font-bold text-gray-900 bg-gradient-to-r from-gray-900 to-blue-900 bg-clip-text text-transparent">Loading Portfolio Data...</p>
                    <p class="text-gray-600 text-sm">Fetching real-time market information</p>
                </div>
            </div>
        </div>
        @endif

        @if(!$isLoading && $portfolio)
        <!-- Real-time Controls -->
        <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 backdrop-blur-sm border border-blue-200 rounded-2xl p-6 mb-8 shadow-2xl transition-all duration-500">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-6">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 border border-blue-200/50 shadow-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse shadow-lg shadow-green-400/50"></div>
                            <span class="text-sm font-semibold text-gray-800">Live Trading</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Last updated</p>
                        <p class="text-sm font-mono font-bold text-gray-900 bg-white/50 px-3 py-1.5 rounded-lg">{{ $lastUpdate }}</p>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <button wire:click="toggleAutoRefresh" 
                            class="group px-5 py-3 rounded-xl transition-all duration-300 flex items-center space-x-3 font-semibold text-sm backdrop-blur-sm border shadow-lg hover:shadow-xl transform hover:-translate-y-0.5
                                   {{ $autoRefresh ? 
                                      'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white border-green-400' : 
                                      'bg-white/80 hover:bg-white border-gray-200 text-gray-700 hover:border-blue-300' }}">
                        <span class="text-sm">{{ $autoRefresh ? '🔴' : '🟢' }}</span>
                        <span>Auto Refresh: {{ $autoRefresh ? 'ON' : 'OFF' }}</span>
                    </button>

                    <!-- Risk Level Badge -->
                    <div class="px-5 py-3 bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200 flex items-center space-x-3 shadow-lg">
                        <span class="text-sm font-semibold text-gray-700">Risk Level:</span>
                        <span class="px-4 py-2 rounded-full text-sm font-bold 
                            {{ $portfolio->risk_mode === 'CONSERVATIVE' ? 'bg-green-500/20 text-green-700 border border-green-400/50' : 
                               ($portfolio->risk_mode === 'MODERATE' ? 'bg-yellow-500/20 text-yellow-700 border border-yellow-400/50' : 
                               'bg-red-500/20 text-red-700 border border-red-400/50') }}">
                            {{ $portfolio->risk_mode }} ({{ $portfolio->risk_value }}%)
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Auto Refresh Polling -->
        @if($autoRefresh)
        <div wire:poll.{{ $refreshInterval }}ms="refreshRealTime" class="hidden"></div>
        @endif

        <!-- Portfolio Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Balance Card -->
            <div class="bg-white/90 backdrop-blur-sm border border-blue-100 rounded-3xl p-6 shadow-2xl hover:shadow-3xl transition-all duration-500 group hover:border-blue-200 cursor-pointer transform hover:-translate-y-2 relative overflow-hidden">
                <div class="absolute -inset-1 bg-gradient-to-br from-blue-400/20 to-indigo-500/20 rounded-3xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex-1">
                        <div class="text-2xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300 mb-1">
                            ${{ number_format($portfolio->balance, 2) }}
                        </div>
                        <div class="text-sm font-semibold text-gray-600 group-hover:text-blue-500 transition-colors duration-300 mb-2">
                            Total Balance
                        </div>
                        <div class="text-xs text-gray-500 font-medium group-hover:text-blue-400 transition-colors duration-300">
                            Initial: ${{ number_format($portfolio->initial_balance, 2) }}
                        </div>
                    </div>
                    
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-xl group-hover:shadow-2xl group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-wallet text-white text-lg"></i>
                        </div>
                    </div>
                </div>
                
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-700 origin-left rounded-full"></div>
            </div>

            <!-- Available Balance Card -->
            <div class="bg-white/90 backdrop-blur-sm border border-emerald-100 rounded-3xl p-6 shadow-2xl hover:shadow-3xl transition-all duration-500 group hover:border-emerald-200 cursor-pointer transform hover:-translate-y-2 relative overflow-hidden">
                <div class="absolute -inset-1 bg-gradient-to-br from-emerald-400/20 to-green-500/20 rounded-3xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex-1">
                        <div class="text-2xl font-bold {{ $this->availableBalanceColor }} group-hover:{{ $portfolio->available_balance > 0 ? 'text-emerald-500' : 'text-red-500' }} transition-colors duration-300 mb-1">
                            ${{ number_format($portfolio->available_balance, 2) }}
                        </div>
                        <div class="text-sm font-semibold text-gray-600 group-hover:text-emerald-500 transition-colors duration-300 mb-2">
                            Available Balance
                        </div>
                        <div class="text-xs font-medium {{ $this->utilizationColor }} transition-colors duration-300">
                            {{ number_format($portfolio->utilization_percentage, 1) }}% utilized
                        </div>
                    </div>
                    
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl {{ $portfolio->available_balance > 0 ? 'bg-gradient-to-br from-emerald-500 to-green-600' : 'bg-gradient-to-br from-red-500 to-rose-600' }} flex items-center justify-center shadow-xl group-hover:shadow-2xl group-hover:scale-110 transition-all duration-300">
                            <i class="fas {{ $portfolio->available_balance > 0 ? 'fa-unlock' : 'fa-lock' }} text-white text-lg"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Utilization Warning -->
                @if($portfolio->is_over_utilized)
                <div class="absolute top-2 right-2">
                    <div class="w-3 h-3 bg-amber-500 rounded-full animate-pulse shadow-lg shadow-amber-400/50"></div>
                </div>
                @endif
                
                <div class="absolute bottom-0 left-0 right-0 h-1 {{ $this->utilizationProgressColor }} transform scale-x-0 group-hover:scale-x-100 transition-transform duration-700 origin-left rounded-full"></div>
            </div>

            <!-- Equity Card -->
            <div class="bg-white/90 backdrop-blur-sm border border-purple-100 rounded-3xl p-6 shadow-2xl hover:shadow-3xl transition-all duration-500 group hover:border-purple-200 cursor-pointer transform hover:-translate-y-2 relative overflow-hidden">
                <div class="absolute -inset-1 bg-gradient-to-br from-purple-400/20 to-indigo-500/20 rounded-3xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex-1">
                        <div class="text-2xl font-bold text-gray-900 group-hover:text-purple-600 transition-colors duration-300 mb-1">
                            ${{ number_format($portfolio->equity, 2) }}
                        </div>
                        <div class="text-sm font-semibold text-gray-600 group-hover:text-purple-500 transition-colors duration-300 mb-2">
                            Total Equity
                        </div>
                        <div class="text-xs font-medium {{ $this->equityChangePercentage >= 0 ? 'text-emerald-600' : 'text-red-600' }} group-hover:{{ $this->equityChangePercentage >= 0 ? 'text-emerald-500' : 'text-red-500' }} transition-colors duration-300">
                            {{ $this->equityChangePercentage >= 0 ? '↗' : '↘' }} {{ number_format(abs($this->equityChangePercentage), 2) }}% from initial
                        </div>
                    </div>
                    
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-xl group-hover:shadow-2xl group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-chart-line text-white text-lg"></i>
                        </div>
                    </div>
                </div>
                
                <div class="absolute bottom-3 left-6 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    <div class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce"></div>
                    <div class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce delay-100"></div>
                    <div class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce delay-200"></div>
                </div>
            </div>

            <!-- Win Rate Card -->
            <div class="bg-white/90 backdrop-blur-sm border border-purple-100 rounded-3xl p-6 shadow-2xl hover:shadow-3xl transition-all duration-500 group hover:border-purple-200 cursor-pointer transform hover:-translate-y-2 relative overflow-hidden">
                <div class="absolute -inset-1 bg-gradient-to-br from-purple-400/20 to-indigo-500/20 rounded-3xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-2xl font-bold {{ $this->winRateColor }} group-hover:text-purple-600 transition-colors duration-300">
                                {{ $this->winRate }}%
                            </div>
                            <div class="text-sm font-semibold text-gray-600 group-hover:text-purple-500 transition-colors duration-300">
                                Win Rate
                            </div>
                            
                            <!-- Enhanced Win Rate Info -->
                            @if($this->advancedWinRate['total_trades'] === 0 && $this->openPositionsCount > 0)
                            <div class="text-xs text-gray-500 mt-1 animate-pulse">
                                Calculating win rate...
                            </div>
                            @elseif($this->advancedWinRate['total_trades'] > 0)
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $this->advancedWinRate['winning_trades'] }}/{{ $this->advancedWinRate['total_trades'] }} wins
                                @if($this->profitFactor > 0)
                                • PF: {{ number_format($this->profitFactor, 2) }}
                                @endif
                            </div>
                            @else
                            <div class="text-xs text-gray-500 mt-1">
                                No trades completed yet
                            </div>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-xl group-hover:shadow-2xl group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-trophy text-white text-lg"></i>
                            </div>
                            
                            <!-- Profit Factor Badge -->
                            @if($this->profitFactor > 0)
                            <div class="absolute -top-1 -right-1 w-6 h-6 {{ $this->profitFactorColor }} bg-white rounded-full flex items-center justify-center text-xs font-bold border-2 border-white shadow-lg">
                                {{ $this->profitFactor >= 10 ? '9+' : number_format($this->profitFactor, 1) }}
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Win Rate Progress Bar -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs text-gray-500 font-medium">
                            <span>Performance</span>
                            <span>
                                @if($this->advancedWinRate['total_trades'] > 0)
                                    {{ $this->advancedWinRate['total_trades'] }} trades
                                @else
                                    No trades
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden shadow-inner">
                            <div class="h-2 rounded-full bg-gradient-to-r {{ $this->winRateProgressColor }} transition-all duration-1000 ease-out shadow-lg" 
                                 style="width: {{ $this->winRate }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span class="{{ $this->winRateColor }} font-bold">
                                {{ $this->winRatePerformance }}
                            </span>
                            <span>
                                @if($this->advancedWinRate['total_trades'] > 0)
                                    W:{{ $this->winningTrades }} L:{{ $this->losingTrades }}
                                @else
                                    --
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-700 origin-left rounded-full"></div>
            </div>
        </div>

        <!-- P&L Performance Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Floating P&L Card -->
            <div class="bg-white/90 backdrop-blur-sm border {{ $this->floatingPnlBorderColor }} rounded-3xl p-6 shadow-2xl hover:shadow-3xl transition-all duration-500 group {{ $this->floatingPnlHoverBorderColor }} cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                <div class="absolute -inset-1 {{ $this->floatingPnl >= 0 ? 'bg-gradient-to-br from-green-400/20 to-emerald-500/20' : 'bg-gradient-to-br from-red-400/20 to-rose-500/20' }} rounded-3xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex-1">
                        <div class="text-3xl font-bold {{ $this->floatingPnlColor }} group-hover:{{ $this->floatingPnl >= 0 ? 'text-green-500' : 'text-red-500' }} transition-colors duration-300 mb-1">
                            ${{ number_format($this->floatingPnl, 2) }}
                        </div>
                        <div class="text-lg font-semibold text-gray-600 group-hover:{{ $this->floatingPnl >= 0 ? 'text-green-500' : 'text-red-500' }} transition-colors duration-300 mb-2">
                            Floating P&L
                        </div>
                        <div class="text-sm font-medium {{ $this->floatingPnlColor }} group-hover:{{ $this->floatingPnl >= 0 ? 'text-green-400' : 'text-red-400' }} transition-colors duration-300">
                            {{ $this->floatingPnlPercentage >= 0 ? '↗' : '↘' }} {{ number_format(abs($this->floatingPnlPercentage), 2) }}%
                        </div>
                    </div>
                    
                    <div class="relative">
                        <div class="w-16 h-16 rounded-2xl {{ $this->floatingPnlBgGradient }} flex items-center justify-center shadow-xl group-hover:shadow-2xl group-hover:scale-110 transition-all duration-300">
                            <i class="fas {{ $this->floatingPnlIcon }} text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r {{ $this->floatingPnlGradientColor }} transform scale-x-0 group-hover:scale-x-100 transition-transform duration-700 origin-left rounded-full"></div>
            </div>

            <!-- Realized P&L Card -->
            <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border {{ $portfolio->realized_pnl >= 0 ? 'border-emerald-100' : 'border-red-100' }} p-6 hover:shadow-3xl transition-all duration-500 group hover:{{ $portfolio->realized_pnl >= 0 ? 'border-emerald-200' : 'border-red-200' }} cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                <div class="absolute -inset-1 {{ $portfolio->realized_pnl >= 0 ? 'bg-gradient-to-br from-emerald-400/20 to-green-500/20' : 'bg-gradient-to-br from-red-400/20 to-rose-500/20' }} rounded-3xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex-1">
                        <div class="text-3xl font-bold {{ $portfolio->realized_pnl >= 0 ? 'text-emerald-600' : 'text-red-600' }} group-hover:{{ $portfolio->realized_pnl >= 0 ? 'text-emerald-500' : 'text-red-500' }} transition-colors duration-300 mb-1">
                            ${{ number_format($portfolio->realized_pnl, 2) }}
                        </div>
                        <div class="text-lg font-semibold text-gray-600 group-hover:{{ $portfolio->realized_pnl >= 0 ? 'text-emerald-500' : 'text-red-500' }} transition-colors duration-300 mb-2">
                            Realized P&L
                        </div>
                        <div class="text-sm font-medium {{ $portfolio->realized_pnl >= 0 ? 'text-emerald-600' : 'text-red-600' }} group-hover:{{ $portfolio->realized_pnl >= 0 ? 'text-emerald-500' : 'text-red-500' }} transition-colors duration-300">
                            {{ $this->realizedPnlPercentage >= 0 ? '↗' : '↘' }} {{ number_format(abs($this->realizedPnlPercentage), 2) }}% from initial
                        </div>
                    </div>
                    
                    <div class="relative">
                        <div class="w-16 h-16 rounded-2xl {{ $portfolio->realized_pnl >= 0 ? 'bg-gradient-to-br from-emerald-500 to-green-600' : 'bg-gradient-to-br from-red-500 to-rose-600' }} flex items-center justify-center shadow-xl group-hover:shadow-2xl group-hover:scale-110 transition-all duration-300">
                            <i class="fas {{ $portfolio->realized_pnl >= 0 ? 'fa-chart-line-up' : 'fa-chart-line-down' }} text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="absolute bottom-0 left-0 right-0 h-1 {{ $portfolio->realized_pnl >= 0 ? 'bg-gradient-to-r from-emerald-500 to-green-600' : 'bg-gradient-to-r from-red-500 to-rose-600' }} transform scale-x-0 group-hover:scale-x-100 transition-transform duration-700 origin-left rounded-full"></div>
            </div>
        </div>

         <!-- Trading Status & Risk Management - IMPROVED VERSION -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- AI Status Card - IMPROVED -->
            <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-8 hover:shadow-3xl transition-all duration-500 group">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-xl group-hover:shadow-2xl transition-all duration-300">
                            <i class="fa-solid fa-robot text-white text-base"></i>
                        </div>
                        <span class="bg-gradient-to-r from-gray-900 to-blue-900 bg-clip-text text-transparent">AI Trading Status</span>
                    </h3>
                    <div class="flex flex-col items-end space-y-2">
                        <span class="px-4 py-2 rounded-full text-sm font-bold {{ $portfolio->ai_trade_enabled ? 'bg-green-500/20 text-green-700 border border-green-400/50' : 'bg-red-500/20 text-red-700 border border-red-400/50' }}">
                            {{ $portfolio->ai_trade_enabled ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                        <!-- Status Indicator -->
                        <div class="flex items-center space-x-2 text-xs {{ $portfolio->can_trade ? 'text-green-600' : 'text-amber-600' }} font-semibold">
                            <div class="w-2 h-2 rounded-full {{ $portfolio->can_trade ? 'bg-green-500 animate-pulse' : 'bg-amber-500' }}"></div>
                            <span>{{ $portfolio->can_trade ? 'Ready to Trade' : 'Trading Limited' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Trading Status Details -->
                <div class="mb-6">
                    @if($portfolio->can_trade)
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-2xl p-4 mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-emerald-800">✅ Trading Available</p>
                                <p class="text-xs text-emerald-600 mt-1">
                                    ${{ number_format($portfolio->available_balance, 2) }} available for new positions
                                </p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-4 mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-amber-800">⚠️ Trading Limited</p>
                                <p class="text-xs text-amber-600 mt-1">
                                    @if($portfolio->available_balance <= 0)
                                        Insufficient available balance
                                    @elseif(!$portfolio->ai_trade_enabled)
                                        AI trading is currently disabled
                                    @elseif($portfolio->equity <= 0)
                                        Portfolio equity is too low
                                    @else
                                        Trading temporarily unavailable
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- AI Activity Stats -->
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-white rounded-2xl p-3 border border-gray-200">
                            <div class="text-lg font-bold text-blue-600">{{ $this->openPositionsCount }}</div>
                            <div class="text-xs text-gray-500 font-semibold">Open Positions</div>
                        </div>
                        <div class="bg-white rounded-2xl p-3 border border-gray-200">
                            <div class="text-lg font-bold text-purple-600">{{ $aiDecisions->count() }}</div>
                            <div class="text-xs text-gray-500 font-semibold">AI Decisions</div>
                        </div>
                    </div>
                </div>

                <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                    {{ $portfolio->ai_trade_enabled ? 
                       'AI is actively monitoring markets and executing trades based on machine learning predictions and real-time analysis.' : 
                       'AI trading is currently disabled. Enable to allow automated trading based on market analysis.' }}
                </p>
                
                <button wire:click="toggleAiTrade" 
                        wire:loading.attr="disabled"
                        class="w-full px-6 py-4 rounded-2xl transition-all duration-300 font-bold shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 flex items-center justify-center space-x-4
                               {{ $portfolio->ai_trade_enabled ? 
                                  'bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white border border-red-400' : 
                                  'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white border border-green-400' }} disabled:opacity-50">
                    <i class="fas {{ $portfolio->ai_trade_enabled ? 'fa-pause-circle' : 'fa-play-circle' }} text-lg"></i>
                    <span class="font-semibold text-base">
                        {{ $portfolio->ai_trade_enabled ? 'Disable AI Trading' : 'Enable AI Trading' }}
                    </span>
                </button>
            </div>

            <!-- Risk Management Card - IMPROVED -->
            <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-8 hover:shadow-3xl transition-all duration-500 group">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-xl group-hover:shadow-2xl transition-all duration-300">
                            <i class="fas fa-shield-alt text-white text-base"></i>
                        </div>
                        <span class="bg-gradient-to-r from-gray-900 to-amber-900 bg-clip-text text-transparent">Risk Management</span>
                    </h3>
                    <span class="px-4 py-2 rounded-full text-sm font-bold 
                        {{ $portfolio->risk_mode === 'CONSERVATIVE' ? 'bg-green-500/20 text-green-700 border border-green-400/50' : 
                           ($portfolio->risk_mode === 'MODERATE' ? 'bg-yellow-500/20 text-yellow-700 border border-yellow-400/50' : 
                           'bg-red-500/20 text-red-700 border border-red-400/50') }}">
                        {{ $portfolio->risk_mode }}
                    </span>
                </div>
                
                <div class="space-y-6">
                    <!-- Risk per Trade -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-200">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Risk per Trade</label>
                                <p class="text-xs text-gray-500 mt-1">Max risk per position</p>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">{{ $portfolio->risk_value }}%</span>
                        </div>
                        <div class="w-full bg-white rounded-full h-3 overflow-hidden shadow-inner border border-blue-100">
                            <div class="h-3 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 transition-all duration-1000 ease-out shadow-lg" 
                                 style="width: {{ min($portfolio->risk_value, 100) }}%">
                            </div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                            <span>Conservative</span>
                            <span>Aggressive</span>
                        </div>
                        <!-- Utilization Warning -->
                        @if($portfolio->is_over_utilized)
                        <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                            <div class="flex items-center space-x-2 text-red-700">
                                <i class="fas fa-exclamation-circle text-sm"></i>
                                <span class="text-sm font-semibold">High Utilization Warning</span>
                            </div>
                            <p class="text-xs text-red-600 mt-1">
                                Consider closing some positions to free up balance
                            </p>
                        </div>
                        @endif
                    </div>

                    <!-- Risk Level Description -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-4 border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Risk Level: {{ $portfolio->risk_mode }}</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            @if($portfolio->risk_mode === 'CONSERVATIVE')
                            Focuses on capital preservation with smaller position sizes and lower risk exposure. Suitable for beginners or risk-averse traders.
                            @elseif($portfolio->risk_mode === 'MODERATE')
                            Balanced approach with moderate position sizes. Aims for steady growth while managing risk appropriately.
                            @else
                            Aggressive strategy with larger position sizes for higher potential returns. Suitable for experienced traders comfortable with higher risk.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-8 mb-8 hover:shadow-3xl transition-all duration-500">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-8">
                <h2 class="text-xl font-bold text-gray-900 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center shadow-xl">
                        <i class="fas fa-sliders-h text-white text-base"></i>
                    </div>
                    <span class="bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">Portfolio Controls</span>
                </h2>
                <div class="flex flex-wrap gap-3 mt-4 lg:mt-0">
                    <button wire:click="refreshPortfolio" 
                            wire:loading.attr="disabled"
                            class="px-5 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 flex items-center space-x-3 text-sm border border-blue-400 disabled:opacity-50">
                        <i class="fas fa-sync-alt text-xs"></i>
                        <span>Refresh Data</span>
                    </button>
                    <button wire:click="resetPortfolio" 
                            wire:confirm="Are you sure you want to reset your portfolio? All positions will be closed and balance reset to initial amount."
                            class="px-5 py-3 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 flex items-center space-x-3 text-sm border border-yellow-400">
                        <i class="fas fa-redo text-xs"></i>
                        <span>Reset Portfolio</span>
                    </button>
                    <button wire:click="closeAllPositions"
                            wire:confirm="Are you sure you want to close ALL open positions?"
                            class="px-5 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 flex items-center space-x-3 text-sm border border-red-400">
                        <i class="fas fa-fire-extinguisher text-xs"></i>
                        <span>Close All Positions</span>
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Initial Balance -->
                <div class="group">
                    <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center space-x-3">
                        <i class="fas fa-wallet text-gray-500 text-sm"></i>
                        <span>Initial Balance ($)</span>
                    </label>
                    <input type="number" step="0.01" wire:model="initialBalance" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-400 transition-all duration-300 bg-white text-sm group-hover:border-blue-400 shadow-lg">
                </div>

                <!-- Risk Mode -->
                <div class="group">
                    <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center space-x-3">
                        <i class="fas fa-chess-knight text-gray-500 text-sm"></i>
                        <span>Risk Mode</span>
                    </label>
                    <select wire:model="riskMode" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-400 transition-all duration-300 bg-white text-sm appearance-none cursor-pointer group-hover:border-blue-400 shadow-lg">
                        <option value="CONSERVATIVE">Conservative (2.5%)</option>
                        <option value="MODERATE">Moderate (5%)</option>
                        <option value="AGGRESSIVE">Aggressive (10%)</option>
                    </select>
                </div>

                <!-- Risk Value -->
                <div class="group">
                    <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center space-x-3">
                        <i class="fas fa-chart-line text-gray-500 text-sm"></i>
                        <span>Custom Risk %</span>
                    </label>
                    <input type="number" step="0.1" min="0.5" max="20" wire:model="riskValue" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-400 transition-all duration-300 bg-white text-sm group-hover:border-blue-400 shadow-lg">
                </div>

                <!-- Update Button -->
                <div class="flex flex-col justify-end">
                    <button wire:click="updatePortfolio" 
                            wire:loading.attr="disabled"
                            class="w-full px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 flex items-center justify-center space-x-3 text-sm border border-green-400 disabled:opacity-50">
                        <i class="fas fa-save text-xs"></i>
                        <span class="font-semibold">Save Changes</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Open Positions -->
        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 overflow-hidden hover:shadow-3xl transition-all duration-500 mb-8">
            <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100/80">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Open Positions</h3>
                        <p class="text-gray-600 text-sm mt-2">Active trading positions managed by AI</p>
                    </div>
                    <div class="text-sm text-gray-700 bg-white/80 px-4 py-2.5 rounded-xl border border-gray-200 font-semibold shadow-lg hover:shadow-xl transition-all duration-300">
                        <span class="text-blue-600 font-bold">{{ $this->openPositionsCount }}</span> active positions
                    </div>
                </div>
            </div>
            
            @if($this->openPositionsCount > 0)
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full text-sm">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                    Symbol & Type
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                    Quantity
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                    Avg Price
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                    Current Price
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                    Investment
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                    Floating P&L
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                    SL/TP
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                    Holding
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($openPositions as $position)
                                @php
                                    $holdingHours = $position->opened_at->diffInHours(now());
                                    $isProfitable = $position->floating_pnl >= 0;
                                @endphp
                                <tr class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-purple-50/50 transition-all duration-300 group cursor-pointer">
                                    <!-- Symbol -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 rounded-xl {{ $position->position_type === 'LONG' ? 'bg-green-500/20' : 'bg-red-500/20' }} flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                                                <i class="fas {{ $position->position_type === 'LONG' ? 'fa-arrow-up text-green-600' : 'fa-arrow-down text-red-600' }} text-sm"></i>
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-900 text-base group-hover:text-blue-600 transition-colors duration-300">{{ $position->symbol }}</span>
                                                <div class="text-sm text-gray-500 capitalize font-semibold">{{ strtolower($position->position_type) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Quantity -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-mono font-semibold text-gray-900 bg-gray-100/50 px-3 py-2 rounded-lg shadow-sm">
                                            {{ number_format($position->qty, 6) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Avg Price -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-mono font-semibold text-gray-900">
                                            ${{ number_format($position->avg_price, 4) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Current Price -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-mono font-semibold text-gray-900">
                                            ${{ number_format($position->current_price, 4) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Investment -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-mono font-semibold text-gray-900 bg-blue-100/50 px-3 py-2 rounded-lg shadow-sm">
                                            ${{ number_format($position->investment, 2) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Floating P&L -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col space-y-2">
                                            <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold transition-all duration-300 {{ $isProfitable ? 'bg-green-500/20 text-green-700 border border-green-200' : 'bg-red-500/20 text-red-700 border border-red-200' }} group-hover:shadow-lg">
                                                <i class="fas {{ $isProfitable ? 'fa-arrow-up mr-2' : 'fa-arrow-down mr-2' }} text-xs"></i>
                                                ${{ number_format($position->floating_pnl, 2) }}
                                            </span>
                                            <span class="text-sm font-semibold {{ $isProfitable ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $position->pnl_percentage >= 0 ? '+' : '' }}{{ number_format($position->pnl_percentage, 2) }}%
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- SL/TP -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-2 text-sm">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-arrow-down text-red-500 text-sm"></i>
                                                <span class="font-mono font-semibold">${{ $position->stop_loss ? number_format($position->stop_loss, 4) : '--' }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-arrow-up text-green-500 text-sm"></i>
                                                <span class="font-mono font-semibold">${{ $position->take_profit ? number_format($position->take_profit, 4) : '--' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Holding -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm font-semibold text-gray-900">{{ $this->getFormattedHoldingTime($position->opened_at) }}</span>  
                                            <div class="w-2.5 h-2.5 {{ $holdingHours < 24 ? 'bg-green-500' : ($holdingHours < 72 ? 'bg-yellow-500' : 'bg-red-500') }} rounded-full shadow-sm"></div>
                                        </div>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <button 
                                                wire:click="closePosition({{ $position->id }})"
                                                wire:confirm="Are you sure you want to close this {{ $position->position_type }} position for {{ $position->symbol }}?"
                                                class="px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white text-sm rounded-lg transition-all duration-300 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center space-x-2"
                                                title="Close Position">
                                                <i class="fas fa-times text-xs"></i>
                                                <span>Close</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-tl from-purple-600/20 to-pink-500/20 rounded-full mb-6">
                        <i class="fas fa-chart-line text-4xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 text-lg font-semibold mb-2">No open positions available.</p>
                    <p class="text-gray-400 text-sm mb-6">When AI makes trading decisions, positions will appear here.</p>
                    @if(!$portfolio->ai_trade_enabled)
                        <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5 inline-flex items-center space-x-4">
                            <i class="fas fa-exclamation-triangle text-orange-500 text-lg"></i>
                            <p class="text-orange-700 text-sm font-semibold">Enable AI Trading to start receiving positions</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Recent Activity Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Recent Trades - Top 10 -->
            <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-8 hover:shadow-3xl transition-all duration-500">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-xl">
                            <i class="fas fa-exchange-alt text-white text-base"></i>
                        </div>
                        <span class="bg-gradient-to-r from-gray-900 to-blue-900 bg-clip-text text-transparent">Recent Trades</span>
                    </h2>
                    <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-bold border border-blue-200">
                        Last 10 Trades
                    </span>
                </div>
                
                @if($recentTrades->count() > 0)
                    <div class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar pr-2">
                        @foreach($recentTrades as $trade)
                            <div class="group p-5 bg-gray-50/50 rounded-2xl hover:bg-white/80 transition-all duration-300 border border-transparent hover:border-blue-200/50 backdrop-blur-sm cursor-pointer transform hover:scale-[1.02]">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 {{ $trade->action === 'BUY' ? 'bg-green-500/20' : 'bg-red-500/20' }} rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                                            <i class="fas {{ $trade->action === 'BUY' ? 'fa-arrow-up text-green-600' : 'fa-arrow-down text-red-600' }} text-base"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-base">{{ $trade->symbol }}</p>
                                            <p class="text-sm text-gray-500">
                                                {{ $trade->position_type ? strtoupper($trade->position_type) . ' • ' : '' }}
                                                {{ $trade->created_at->format('M j, H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900 text-base">${{ number_format($trade->amount, 2) }}</p>
                                        <p class="text-sm font-semibold {{ $trade->pnl >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            @if($trade->pnl !== null)
                                                {{ $trade->pnl >= 0 ? '+' : '' }}${{ number_format($trade->pnl, 2) }}
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <!-- Additional Trade Info -->
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <span>Qty: {{ number_format($trade->quantity ?? 0, 6) }}</span>
                                    <span>Price: ${{ number_format($trade->price ?? 0, 4) }}</span>
                                    <span class="{{ $trade->status === 'closed' ? 'text-green-600' : 'text-blue-600' }} font-semibold">
                                        {{ strtoupper($trade->status ?? 'completed') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-tl from-blue-600/20 to-cyan-500/20 rounded-full mb-4">
                            <i class="fas fa-exchange-alt text-3xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 text-base font-medium">No trades yet</p>
                        <p class="text-gray-400 text-sm mt-2">Trade history will appear here</p>
                    </div>
                @endif
            </div>

            <!-- AI Decisions - Top 10 -->
            <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-8 hover:shadow-3xl transition-all duration-500">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-xl">
                            <i class="fas fa-brain text-white text-base"></i>
                        </div>
                        <span class="bg-gradient-to-r from-gray-900 to-purple-900 bg-clip-text text-transparent">Recent AI Decisions</span>
                    </h2>
                    <span class="px-4 py-2 bg-purple-100 text-purple-700 rounded-full text-sm font-bold border border-purple-200">
                        Last 10 Decisions
                    </span>
                </div>
                
                @if($aiDecisions->count() > 0)
                    <div class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar pr-2">
                        @foreach($aiDecisions as $decision)
                            <div class="group p-5 border border-gray-200/50 rounded-2xl hover:border-purple-300/50 transition-all duration-300 bg-white/50 hover:bg-white/80 backdrop-blur-sm cursor-pointer transform hover:scale-[1.02]">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-4">
                                        <span class="px-3 py-2 rounded-full text-sm font-bold border
                                                    {{ $decision->action === 'BUY' ? 'bg-green-500/20 text-green-700 border-green-400/50' : 
                                                       ($decision->action === 'SELL' ? 'bg-red-500/20 text-red-700 border-red-400/50' : 
                                                       'bg-gray-500/20 text-gray-700 border-gray-400/50') }}">
                                            {{ $decision->action }}
                                        </span>
                                        <span class="font-bold text-gray-900 text-base">{{ $decision->symbol }}</span>
                                        <div class="flex items-center space-x-2" title="Confidence Level">
                                            <div class="w-2 h-2 
                                                {{ $decision->confidence >= 80 ? 'bg-green-500' : 
                                                   ($decision->confidence >= 60 ? 'bg-yellow-500' : 'bg-red-500') }} 
                                                rounded-full animate-pulse"></div>
                                            <span class="text-sm text-gray-600 font-semibold">{{ $decision->confidence }}%</span>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500" title="{{ $decision->created_at->format('M j, Y H:i') }}">
                                        {{ $decision->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-700 leading-relaxed line-clamp-2 mb-3">
                                    {{ $decision->explanation }}
                                </p>
                                
                                <!-- Additional Decision Info -->
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-dollar-sign text-gray-400"></i>
                                        <span>${{ number_format($decision->price, 4) }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                        <span>{{ $decision->decision_time->format('M j, H:i') }}</span>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $decision->executed ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $decision->executed ? 'Executed' : 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-tl from-purple-600/20 to-pink-500/20 rounded-full mb-4">
                            <i class="fas fa-brain text-3xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 text-base font-medium">No AI decisions yet</p>
                        <p class="text-gray-400 text-sm mt-2">AI decisions will appear here when generated</p>
                        @if(!$portfolio->ai_trade_enabled)
                            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 inline-flex items-center space-x-3 mt-4">
                                <i class="fas fa-exclamation-triangle text-orange-500"></i>
                                <p class="text-orange-700 text-sm font-semibold">Enable AI Trading to start receiving decisions</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Adaptive Learning Insights Section -->
        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-8 mb-8 hover:shadow-3xl transition-all duration-500">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold text-gray-900 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-xl">
                        <i class="fas fa-lightbulb text-white text-base"></i>
                    </div>
                    <span class="bg-gradient-to-r from-gray-900 to-indigo-900 bg-clip-text text-transparent">AI Insights & Optimization</span>
                </h2>
                <button wire:click="refreshOptimization" 
                        wire:loading.attr="disabled"
                        class="px-5 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl transition-all duration-300 font-semibold shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 flex items-center space-x-3 text-sm border border-indigo-400">
                    <i class="fas fa-sync-alt text-xs"></i>
                    <span>Refresh Insights</span>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Optimization Recommendations -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center space-x-3">
                        <i class="fas fa-magic text-indigo-500"></i>
                        <span>Optimization Recommendations</span>
                    </h3>
                    
                    @if(count($optimizationRecommendations) > 0)
                        <div class="space-y-4">
                            @foreach($optimizationRecommendations as $index => $recommendation)
                                <div class="group p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100 hover:border-indigo-200 transition-all duration-300 cursor-pointer transform hover:scale-[1.02]">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0 mt-1 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-bullseye text-white text-xs"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-800 leading-relaxed">{{ $recommendation }}</p>
                                            <div class="flex items-center justify-between mt-3">
                                                <span class="text-xs text-indigo-600 font-semibold">AI Suggestion</span>
                                                <button wire:click="applyOptimization({{ $index }})" 
                                                        class="text-xs bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-lg transition-colors duration-200 font-semibold">
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-tl from-indigo-600/20 to-purple-500/20 rounded-full mb-4">
                                <i class="fas fa-lightbulb text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 text-base font-medium">No recommendations available</p>
                            <p class="text-gray-400 text-sm mt-2">AI will provide optimization tips based on your trading patterns</p>
                        </div>
                    @endif
                </div>

                <!-- Performance Metrics -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center space-x-3">
                        <i class="fas fa-chart-bar text-green-500"></i>
                        <span>Performance Analytics</span>
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Risk Adjusted Metrics -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="text-2xl font-bold text-gray-900 mb-1">{{ $this->calculateExpectancy() }}</div>
                                <div class="text-xs text-gray-500 font-semibold">Expectancy</div>
                            </div>
                            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="text-2xl font-bold text-gray-900 mb-1">{{ $this->calculateAvgRiskRewardRatio() }}</div>
                                <div class="text-xs text-gray-500 font-semibold">Avg R:R Ratio</div>
                            </div>
                        </div>

                        <!-- Performance Grade -->
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-5 border border-green-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-700 mb-1">Performance Grade</div>
                                    <div class="text-xs text-gray-500">Based on win rate & profit factor</div>
                                </div>
                                <div class="text-3xl font-bold {{ $this->performanceGrade['color'] }} bg-white rounded-full w-16 h-16 flex items-center justify-center border-4 {{ $this->performanceGrade['bg'] }}">
                                    {{ $this->performanceGrade['grade'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Improvement Tips -->
                        @if(count($this->advancedWinRate['improvement_tips']) > 0)
                            <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl p-5 border border-blue-200">
                                <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-graduation-cap text-blue-500"></i>
                                    <span>Improvement Tips</span>
                                </h4>
                                <ul class="space-y-2">
                                    @foreach($this->advancedWinRate['improvement_tips'] as $tip)
                                        <li class="text-sm text-gray-700 flex items-start space-x-2">
                                            <i class="fas fa-check text-green-500 mt-1 text-xs"></i>
                                            <span>{{ $tip }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Analytics Section -->
        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100 p-8 mb-8 hover:shadow-3xl transition-all duration-500">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold text-gray-900 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center shadow-xl">
                        <i class="fas fa-chart-line text-white text-base"></i>
                    </div>
                    <span class="bg-gradient-to-r from-gray-900 to-teal-900 bg-clip-text text-transparent">Advanced Analytics</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Win Rate by Position Type -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Win Rate by Position Type</h3>
                    <div class="space-y-4">
                        @foreach($this->winRateByPositionType as $type => $stats)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full {{ $type === 'LONG' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span class="text-sm font-semibold text-gray-700">{{ $type }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold {{ $stats['win_rate'] >= 50 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $stats['win_rate'] }}%
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $stats['total_trades'] }} trades</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Monthly Performance -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Monthly Performance</h3>
                    <div class="text-center">
                        <div class="text-3xl font-bold {{ $this->monthlyWinRate >= 50 ? 'text-green-600' : 'text-red-600' }} mb-2">
                            {{ $this->monthlyWinRate }}%
                        </div>
                        <div class="text-sm text-gray-600">Current Month Win Rate</div>
                        <div class="mt-4 text-xs text-gray-500">
                            @php
                                $monthlyTrend = $this->getPerformanceTrend();
                            @endphp
                            <div class="flex items-center justify-center space-x-2 {{ $monthlyTrend['direction'] === 'up' ? 'text-green-600' : 'text-red-600' }}">
                                <i class="fas fa-arrow-{{ $monthlyTrend['direction'] === 'up' ? 'up' : 'down' }}"></i>
                                <span>{{ number_format(abs($monthlyTrend['trend']), 1) }}% from last week</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Risk Adjusted Metrics -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Risk Metrics</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">Profit Factor</span>
                            <span class="text-lg font-bold {{ $this->profitFactorColor }}">
                                {{ number_format($this->profitFactor, 2) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">Sharpe Ratio</span>
                            <span class="text-lg font-bold text-gray-900">
                                {{ number_format($this->calculateSharpeRatio(), 2) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">Consistency</span>
                            <span class="text-lg font-bold text-gray-900">
                                {{ $this->calculateConsistencyScore() }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @elseif(!$isLoading && !$portfolio)
        <!-- No Portfolio State -->
        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-3xl p-16 text-center border border-gray-100">
            <div class="text-7xl mb-8 opacity-60">
                <i class="fas fa-chart-pie text-gray-400"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-6 bg-gradient-to-r from-gray-900 to-blue-900 bg-clip-text text-transparent">No Portfolio Found</h2>
            <p class="text-gray-600 mb-10 text-base max-w-md mx-auto leading-relaxed">Create your first portfolio to start AI-powered trading with advanced risk management and real-time analytics.</p>
            <button wire:click="createPortfolio" 
                    class="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-2xl transition-all duration-300 font-bold shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 border border-white/20 text-base">
                <i class="fas fa-plus mr-3"></i>
                Create Your First Portfolio
            </button>
        </div>
        @endif

        <!-- Debug Panel (Hidden by default) -->
        <div class="mt-8 text-center">
            <button onclick="document.getElementById('debugPanel').classList.toggle('hidden')" 
                    class="text-xs text-gray-500 hover:text-gray-700 transition-colors duration-200">
                <i class="fas fa-bug mr-1"></i>
                Toggle Debug Info
            </button>
        </div>

        <div id="debugPanel" class="hidden mt-4 bg-gray-900 text-gray-200 rounded-2xl p-6 font-mono text-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Debug Information</h3>
                <button wire:click="debugPortfolio" 
                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 rounded-lg text-xs">
                    Refresh Debug
                </button>
            </div>
            <pre>{{ json_encode($this->portfolio_summary, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 10px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
        border-radius: 10px;
        border: 2px solid #f8fafc;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 10px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #cbd5e1 0%, #94a3b8 100%);
        border-radius: 10px;
        border: 2px solid #f8fafc;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(90deg, #94a3b8 0%, #64748b 100%);
    }

    /* Animations */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .refreshing {
        animation: spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
    }

    /* Line clamp utility */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Backdrop blur */
    .backdrop-blur-sm {
        backdrop-filter: blur(12px);
    }

    /* Smooth transitions */
    .transition-all {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Gradient text */
    .bg-gradient-text {
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Custom glow effects */
    .glow-blue {
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    }
    
    .glow-green {
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
    }
    
    .glow-purple {
        box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto refresh notification
    document.addEventListener('livewire:load', function () {
        Livewire.on('notify', (data) => {
            if (data.type && data.message) {
                // You can add toast notifications here if needed
                console.log(`${data.type}: ${data.message}`);
            }
        });
        
        // Refresh winrate when positions change
        Livewire.on('positionClosed', () => {
            console.log('Position closed - winrate should update');
        });

        // Auto-refresh functionality
        let autoRefreshInterval;
        
        Livewire.on('autoRefreshToggled', (enabled) => {
            if (enabled && !autoRefreshInterval) {
                autoRefreshInterval = setInterval(() => {
                    Livewire.dispatch('refreshRealTime');
                }, {{ $refreshInterval }});
            } else {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        });
    });

    // Smooth scrolling for tables
    function smoothScrollTable(tableId) {
        const table = document.getElementById(tableId);
        if (table) {
            table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
</script>
@endpush