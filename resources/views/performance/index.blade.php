<x-layouts.base>
    @auth
        @include('layouts.navbars.auth.sidebar')
        <main class="ease-soft-in-out xl:ml-68.5 relative h-full max-h-screen rounded-xl transition-all duration-200">
            @include('layouts.navbars.auth.nav')
            <div class="w-full px-6 py-6 mx-auto">
                
                <!-- Page Header - Clean dan Elegan -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <div class="relative group">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-3 shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                                    <i class="fas fa-chart-line text-white text-sm"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">Performance Tracker</h1>
                                <p class="text-gray-500 text-xs mt-1">Real-time cryptocurrency performance metrics and health scores</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
                        <!-- Search Form -->
                        <form method="GET" action="{{ route('performance.index') }}" class="relative w-full sm:w-48 group">
                            @if(request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif
                            @if(request('direction'))
                                <input type="hidden" name="direction" value="{{ request('direction') }}">
                            @endif
                            @if(request('per_page'))
                                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                            @endif
                            @if(request('filter'))
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            @endif
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-xs group-hover:text-blue-500 transition-colors duration-300"></i>
                            </div>
                            <input id="symbol" name="symbol" type="text" 
                                   class="w-full bg-white border border-gray-200 rounded-xl pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition-all duration-300 shadow-sm hover:shadow-md group-hover:border-blue-300" 
                                   value="{{ request('symbol') }}" placeholder="TON, BTC...">
                            @if(request('symbol'))
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <a href="{{ route('performance.index', request()->except(['symbol', 'page'])) }}" 
                                   class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-times text-xs"></i>
                                </a>
                            </div>
                            @endif
                        </form>
                        <form action="{{ route('performance.refresh') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-gradient-to-br from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-medium py-2.5 px-4 rounded-xl flex items-center text-xs transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0 group">
                                <i class="fas fa-sync-alt mr-2 text-xs group-hover:rotate-180 transition-transform duration-500"></i>
                                Refresh
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Alert Messages - Clean dengan Efek -->
                @if(session('success'))
                    <div class="mb-6 bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center shadow-lg hover:shadow-xl transition-all duration-300" role="alert">
                        <div class="w-4 h-4 rounded-full bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center mr-3 flex-shrink-0 shadow-sm">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <span class="font-semibold">Success!</span> {{ session('success') }}
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600 ml-3 transition-colors duration-200">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-gradient-to-r from-rose-50 to-red-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm flex items-center shadow-lg hover:shadow-xl transition-all duration-300" role="alert">
                        <div class="w-4 h-4 rounded-full bg-gradient-to-br from-rose-500 to-red-500 flex items-center justify-center mr-3 flex-shrink-0 shadow-sm">
                            <i class="fas fa-exclamation text-white text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <span class="font-semibold">Error!</span> {{ session('error') }}
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-600 ml-3 transition-colors duration-200">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                @endif

                <!-- Active Filters Display -->
                @if(request()->hasAny(['symbol', 'filter']))
                <div class="mb-6 bg-gradient-to-r from-blue-50/80 to-indigo-50/80 backdrop-blur-sm border border-blue-100 rounded-xl p-4 shadow-lg transition-all duration-500">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="text-sm font-semibold text-blue-800">Active Filters:</span>
                            @if(request('filter') == 'high_health')
                                <a href="{{ route('performance.index', request()->except(['filter', 'page'])) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-emerald-500 to-green-500 text-white text-xs rounded-full font-medium hover:from-emerald-600 hover:to-green-600 transition-all duration-300">
                                    <i class="fas fa-heart mr-1 text-xs"></i>
                                    Health > 70
                                    <i class="fas fa-times ml-2 text-xs"></i>
                                </a>
                            @endif
                            @if(request('filter') == 'high_trend')
                                <a href="{{ route('performance.index', request()->except(['filter', 'page'])) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs rounded-full font-medium hover:from-amber-600 hover:to-orange-600 transition-all duration-300">
                                    <i class="fas fa-trending-up mr-1 text-xs"></i>
                                    Trend > 70
                                    <i class="fas fa-times ml-2 text-xs"></i>
                                </a>
                            @endif
                            @if(request('symbol'))
                                <a href="{{ route('performance.index', request()->except(['symbol', 'page'])) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs rounded-full font-medium hover:from-purple-600 hover:to-pink-600 transition-all duration-300">
                                    <i class="fas fa-search mr-1 text-xs"></i>
                                    Symbol: {{ request('symbol') }}
                                    <i class="fas fa-times ml-2 text-xs"></i>
                                </a>
                            @endif
                        </div>
                        @if(request()->hasAny(['symbol', 'filter']))
                        <a href="{{ route('performance.index') }}" 
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Clear All
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Stats Cards - Clean Premium dengan Floating Elements -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <!-- Total Coins - Clean Blue -->
                    <a href="{{ route('performance.index', request()->except(['filter', 'page'])) }}" 
                       class="bg-white border {{ !request('filter') ? 'border-blue-300 shadow-xl' : 'border-blue-100' }} rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-blue-200 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                        <!-- Subtle Background Glow -->
                        <div class="absolute -inset-0.5 bg-gradient-to-br from-blue-400/10 to-indigo-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        
                        <div class="relative z-10 flex items-center justify-between">
                            <!-- Content -->
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors duration-300">{{ $totalCount }}</div>
                                <div class="text-sm font-semibold text-gray-600 group-hover:text-blue-500 transition-colors duration-300 mt-1">Total Coins</div>
                                <div class="text-xs text-gray-400 font-medium group-hover:text-blue-400 transition-colors duration-300 mt-2">Live updates</div>
                            </div>
                            
                            <!-- Clean Icon -->
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                    <i class="fas fa-coins text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Clean Progress Bar -->
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-500/80 to-indigo-600/80 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-700 origin-left rounded-full"></div>
                    </a>

                    <!-- High Health - Clean Green -->
                    <a href="{{ route('performance.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'high_health'])) }}" 
                       class="bg-white border {{ request('filter') == 'high_health' ? 'border-emerald-300 shadow-xl' : 'border-emerald-100' }} rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-emerald-200 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                        <!-- Subtle Background Glow -->
                        <div class="absolute -inset-0.5 bg-gradient-to-br from-emerald-400/10 to-green-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        
                        <div class="relative z-10 flex items-center justify-between">
                            <!-- Content -->
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-gray-800 group-hover:text-emerald-600 transition-colors duration-300">{{ $highHealth }}</div>
                                <div class="text-sm font-semibold text-gray-600 group-hover:text-emerald-500 transition-colors duration-300 mt-1">High Health</div>
                                <div class="text-xs text-gray-400 font-medium group-hover:text-emerald-400 transition-colors duration-300 mt-2">Health > 70</div>
                            </div>
                            
                            <!-- Clean Icon -->
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                    <i class="fas fa-heart text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress Dots -->
                        <div class="absolute bottom-3 left-5 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            <div class="w-1 h-1 bg-emerald-400 rounded-full animate-bounce"></div>
                            <div class="w-1 h-1 bg-emerald-400 rounded-full animate-bounce delay-100"></div>
                            <div class="w-1 h-1 bg-emerald-400 rounded-full animate-bounce delay-200"></div>
                        </div>
                    </a>

                    <!-- High Trend - Clean Amber -->
                    <a href="{{ route('performance.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'high_trend'])) }}" 
                       class="bg-white border {{ request('filter') == 'high_trend' ? 'border-amber-300 shadow-xl' : 'border-amber-100' }} rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-amber-200 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                        <!-- Subtle Background Glow -->
                        <div class="absolute -inset-0.5 bg-gradient-to-br from-amber-400/10 to-orange-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        
                        <div class="relative z-10 flex items-center justify-between">
                            <!-- Content -->
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-gray-800 group-hover:text-amber-600 transition-colors duration-300">{{ $highTrend }}</div>
                                <div class="text-sm font-semibold text-gray-600 group-hover:text-amber-500 transition-colors duration-300 mt-1">High Trend</div>
                                <div class="text-xs text-gray-400 font-medium group-hover:text-amber-400 transition-colors duration-300 mt-2">Trend > 70</div>
                            </div>
                            
                            <!-- Clean Icon -->
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                    <i class="fas fa-star text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gold Accent -->
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-amber-400/80 to-orange-500/80 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-700 origin-left rounded-full"></div>
                    </a>

                    <!-- Positive Gain - Clean Emerald -->
                    <div class="bg-white border border-teal-100 rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-teal-200 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                        <!-- Subtle Background Glow -->
                        <div class="absolute -inset-0.5 bg-gradient-to-br from-teal-400/10 to-emerald-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        
                        <div class="relative z-10 flex items-center justify-between">
                            <!-- Content -->
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-gray-800 group-hover:text-teal-600 transition-colors duration-300">{{ $positivePerformance }}</div>
                                <div class="text-sm font-semibold text-gray-600 group-hover:text-teal-500 transition-colors duration-300 mt-1">Positive Gain</div>
                                <div class="text-xs text-gray-400 font-medium group-hover:text-teal-400 transition-colors duration-300 mt-2">Growing assets</div>
                            </div>
                            
                            <!-- Clean Icon -->
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                    <i class="fas fa-chart-line text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Security Line -->
                        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-teal-400/80 to-emerald-500/80 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-700 origin-left rounded-full"></div>
                    </div>
                </div>

                <!-- Performance Table - Clean dengan Interaktivitas -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-500">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Performance Metrics</h3>
                                <p class="text-gray-500 text-xs mt-1">Real-time cryptocurrency performance data</p>
                            </div>
                            <div class="text-xs text-gray-600 bg-white px-3 py-1.5 rounded-lg border border-gray-200 font-medium shadow-sm hover:shadow-md transition-all duration-300">
                                <span class="text-blue-600 font-semibold">{{ $performances->total() }}</span> coins tracked
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full text-sm">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <a href="{{ route('performance.index', array_merge(request()->except(['sort', 'direction', 'page']), [
                                            'sort' => 'symbol', 
                                            'direction' => request('sort') == 'symbol' && request('direction') == 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-coins text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Symbol</span>
                                            @if(request('sort') == 'symbol')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs ml-2 text-blue-500 animate-pulse"></i>
                                            @else
                                                <i class="fas fa-sort text-xs ml-2 text-gray-400 group-hover:text-gray-600 transition-colors duration-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <a href="{{ route('performance.index', array_merge(request()->except(['sort', 'direction', 'page']), [
                                            'sort' => 'performance_since_first', 
                                            'direction' => request('sort') == 'performance_since_first' && request('direction') == 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-chart-line text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Performance</span>
                                            @if(request('sort') == 'performance_since_first')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs ml-2 text-blue-500 animate-pulse"></i>
                                            @else
                                                <i class="fas fa-sort text-xs ml-2 text-gray-400 group-hover:text-gray-600 transition-colors duration-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <a href="{{ route('performance.index', array_merge(request()->except(['sort', 'direction', 'page']), [
                                            'sort' => 'trend_strength', 
                                            'direction' => request('sort') == 'trend_strength' && request('direction') == 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-trending-up text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Trend</span>
                                            @if(request('sort') == 'trend_strength')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs ml-2 text-blue-500 animate-pulse"></i>
                                            @else
                                                <i class="fas fa-sort text-xs ml-2 text-gray-400 group-hover:text-gray-600 transition-colors duration-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <a href="{{ route('performance.index', array_merge(request()->except(['sort', 'direction', 'page']), [
                                            'sort' => 'momentum_phase', 
                                            'direction' => request('sort') == 'momentum_phase' && request('direction') == 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-rocket text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Momentum</span>
                                            @if(request('sort') == 'momentum_phase')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs ml-2 text-blue-500 animate-pulse"></i>
                                            @else
                                                <i class="fas fa-sort text-xs ml-2 text-gray-400 group-hover:text-gray-600 transition-colors duration-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <a href="{{ route('performance.index', array_merge(request()->except(['sort', 'direction', 'page']), [
                                            'sort' => 'risk_level', 
                                            'direction' => request('sort') == 'risk_level' && request('direction') == 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-shield-alt text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Risk</span>
                                            @if(request('sort') == 'risk_level')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs ml-2 text-blue-500 animate-pulse"></i>
                                            @else
                                                <i class="fas fa-sort text-xs ml-2 text-gray-400 group-hover:text-gray-600 transition-colors duration-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <a href="{{ route('performance.index', array_merge(request()->except(['sort', 'direction', 'page']), [
                                            'sort' => 'hours_since_first', 
                                            'direction' => request('sort') == 'hours_since_first' && request('direction') == 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-clock text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Since</span>
                                            @if(request('sort') == 'hours_since_first')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs ml-2 text-blue-500 animate-pulse"></i>
                                            @else
                                                <i class="fas fa-sort text-xs ml-2 text-gray-400 group-hover:text-gray-600 transition-colors duration-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                        <i class="fas fa-arrow-right text-gray-400 text-xs mr-2"></i>
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($performances as $performance)
                                <tr class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-purple-50/50 transition-all duration-300 group cursor-pointer transform hover:scale-[1.01]">
                                    <!-- Symbol -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition-colors duration-300">{{ $performance->symbol }}</div>
                                        </div>
                                        <div class="text-gray-500 text-xs mt-1 group-hover:text-gray-600 transition-colors duration-300">${{ number_format($performance->current_price, 4) }}</div>
                                    </td>
                                    
                                    <!-- Performance -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold transition-all duration-300 {{ $performance->performance_since_first >= 0 ? 'bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 border border-green-200' : 'bg-gradient-to-r from-red-500/20 to-rose-500/20 text-red-700 border border-red-200' }} group-hover:shadow-md">
                                            <i class="fas {{ $performance->performance_since_first >= 0 ? 'fa-arrow-up mr-1' : 'fa-arrow-down mr-1' }} text-xs"></i>
                                            {{ $performance->performance_since_first >= 0 ? '+' : '' }}{{ number_format($performance->performance_since_first, 2) }}%
                                        </span>
                                    </td>
                                    
                                    <!-- Trend -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 shadow-inner group-hover:w-18 transition-all duration-500">
                                                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 h-2 rounded-full transition-all duration-1000 ease-out" style="width: {{ $performance->trend_strength }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-700 min-w-[35px] group-hover:text-blue-600 transition-colors duration-300">{{ $performance->trend_strength }}</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Momentum -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $momentumColors = [
                                                'ACCUMULATION' => 'bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 border border-green-200',
                                                'DISTRIBUTION' => 'bg-gradient-to-r from-yellow-500/20 to-amber-500/20 text-yellow-700 border border-yellow-200'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold transition-all duration-300 {{ $momentumColors[$performance->momentum_phase] ?? 'bg-gradient-to-r from-gray-500/20 to-gray-600/20 text-gray-700 border border-gray-200' }} group-hover:shadow-md">
                                            {{ $performance->momentum_phase }}
                                        </span>
                                    </td>
                                    
                                    <!-- Risk -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $riskColors = [
                                                'LOW' => 'bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 border border-green-200',
                                                'MEDIUM' => 'bg-gradient-to-r from-yellow-500/20 to-amber-500/20 text-yellow-700 border border-yellow-200',
                                                'HIGH' => 'bg-gradient-to-r from-red-500/20 to-rose-500/20 text-red-700 border border-red-200'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold transition-all duration-300 {{ $riskColors[$performance->risk_level] ?? 'bg-gradient-to-r from-gray-500/20 to-gray-600/20 text-gray-700 border border-gray-200' }} group-hover:shadow-md">
                                            {{ $performance->risk_level }}
                                        </span>
                                    </td>
                                    
                                    <!-- Since -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 group-hover:text-gray-700 transition-colors duration-300">
                                            {{ number_format($performance->hours_since_first, 1) }}h
                                        </div>
                                        <div class="text-xs text-gray-500 group-hover:text-gray-600 transition-colors duration-300">
                                            tracked
                                        </div>
                                    </td>
                                    
                                    <!-- Action -->
                                    <td class="px-4 py-3 whitespace-nowrap text-xs font-semibold">
                                        <a href="{{ route('performance.show', $performance->id) }}" 
                                           class="bg-gradient-to-br from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white py-2 px-3 rounded-lg flex items-center transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0 group">
                                            <i class="fas fa-eye mr-2 text-xs"></i>
                                            View
                                            <i class="fas fa-arrow-right ml-1 text-xs opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination - Clean dengan Efek -->
                    @if($performances->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-xs text-gray-600 font-medium">
                                Showing <span class="text-gray-900 font-semibold">{{ $performances->firstItem() }}</span> to 
                                <span class="text-gray-900 font-semibold">{{ $performances->lastItem() }}</span> of 
                                <span class="text-gray-900 font-semibold">{{ $performances->total() }}</span> results
                            </div>
                            
                            <!-- Pagination Links -->
                            <div class="flex items-center space-x-1">
                                <!-- Previous Page Link -->
                                @if ($performances->onFirstPage())
                                    <span class="px-3 py-1.5 text-xs text-gray-500 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                        <i class="fas fa-chevron-left mr-1"></i>Previous
                                    </span>
                                @else
                                    <a href="{{ $performances->appends(request()->query())->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-all duration-300 group shadow-sm hover:shadow-md">
                                        <i class="fas fa-chevron-left mr-1 group-hover:-translate-x-0.5 transition-transform duration-300"></i>Previous
                                    </a>
                                @endif

                                <!-- Page Numbers -->
                                @foreach ($performances->getUrlRange(1, $performances->lastPage()) as $page => $url)
                                    @if ($page == $performances->currentPage())
                                        <span class="px-3 py-1.5 text-xs text-white bg-gradient-to-tl from-purple-600 to-pink-500 border border-transparent rounded-lg shadow-md">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url . '&' . http_build_query(request()->except('page')) }}" class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md">{{ $page }}</a>
                                    @endif
                                @endforeach

                                <!-- Next Page Link -->
                                @if ($performances->hasMorePages())
                                    <a href="{{ $performances->appends(request()->query())->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-all duration-300 group shadow-sm hover:shadow-md">
                                        Next<i class="fas fa-chevron-right ml-1 group-hover:translate-x-0.5 transition-transform duration-300"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-1.5 text-xs text-gray-500 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                        Next<i class="fas fa-chevron-right ml-1"></i>
                                    </span>
                                @endif
                            </div>

                            <!-- Items Per Page Selector -->
                            <div class="flex items-center space-x-2 text-xs text-gray-600">
                                <span>Items per page:</span>
                                <div class="relative group">
                                    <select onchange="changeItemsPerPage(this.value)" class="bg-white border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500/50 transition-all duration-300 appearance-none cursor-pointer group-hover:shadow-md">
                                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                        <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Empty State -->
                    @if($performances->isEmpty())
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-tl from-purple-600/20 to-pink-500/20 rounded-full mb-4">
                            <i class="fas fa-chart-line text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-400 text-sm">No performance data available.</p>
                        <p class="text-gray-500 text-xs mt-1">Click "Refresh" to fetch data from the API.</p>
                    </div>
                    @endif
                </div>

                @include('layouts.footers.auth.footer')
            </div>
        </main>
    @else
        @include('layouts.navbars.guest.nav')
        <div class="w-full px-6 py-6 mx-auto">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800">Performance Tracker</h1>
                <p class="text-gray-600 mt-2">Please login to view performance data</p>
            </div>
        </div>
        @include('layouts.footers.guest.footer')
    @endauth

    @push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom Scrollbar dengan Efek */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-track {
            background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 8px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #cbd5e1 0%, #94a3b8 100%);
            border-radius: 8px;
            border: 1px solid #f8fafc;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(90deg, #94a3b8 0%, #64748b 100%);
        }

        /* Animasi Smooth */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .refreshing {
            animation: spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }

        /* Hover Effects yang Lebih Halus */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
        }

        /* Animasi Stagger untuk Table Rows */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        tbody tr {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        tbody tr:nth-child(odd) {
            animation-delay: 0.05s;
        }

        tbody tr:nth-child(even) {
            animation-delay: 0.1s;
        }

        /* Custom animations for cards */
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }
    </style>
    @endpush
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced refresh button dengan animasi yang lebih smooth
            const refreshButton = document.querySelector('button[type="submit"]');
            if (refreshButton) {
                refreshButton.addEventListener('click', function(e) {
                    const icon = this.querySelector('i');
                    const text = this.querySelector('span');
                    
                    // Add loading state
                    this.disabled = true;
                    icon.classList.add('refreshing');
                    if (text) text.textContent = 'Refreshing...';
                    
                    // Revert after 2 seconds
                    setTimeout(() => {
                        this.disabled = false;
                        icon.classList.remove('refreshing');
                        if (text) text.textContent = 'Refresh';
                    }, 2000);
                });
            }

            // Enhanced search dengan efek visual
            const searchInput = document.getElementById('symbol');
            if (searchInput) {
                let searchTimeout;
                
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        // Submit the form when typing stops
                        this.form.submit();
                    }, 800);
                });

                // Add enter key support
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(searchTimeout);
                        this.form.submit();
                    }
                });
            }

            // Add click effects to cards
            const cards = document.querySelectorAll('.bg-white');
            cards.forEach(card => {
                card.addEventListener('mousedown', function() {
                    this.style.transform = 'scale(0.98) translateY(-1px)';
                });
                
                card.addEventListener('mouseup', function() {
                    this.style.transform = 'translateY(-1px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Close alert functionality dengan animasi
            const alertCloseButtons = document.querySelectorAll('[role="alert"] button');
            alertCloseButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const alert = this.closest('[role="alert"]');
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                });
            });

            // Add ripple effect to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('click', function(e) {
                    // Create ripple element
                    const ripple = document.createElement('div');
                    ripple.className = 'absolute inset-0 bg-blue-500 opacity-10 rounded-lg animate-ping';
                    ripple.style.animationDuration = '0.6s';
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    // Remove ripple after animation
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        function changeItemsPerPage(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            // Reset to page 1 when changing items per page
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }

        // Simple filter functions for active filter badges
        function removeSymbolFilter() {
            window.location.href = "{{ route('performance.index', request()->except(['symbol', 'page'])) }}";
        }

        function clearAllFilters() {
            window.location.href = "{{ route('performance.index') }}";
        }
    </script>
    @endpush
</x-layouts.base>