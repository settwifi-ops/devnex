<x-layouts.base>
    @auth
        @include('layouts.navbars.auth.sidebar')
        <main class="ease-soft-in-out xl:ml-68.5 relative h-full max-h-screen rounded-xl transition-all duration-200">
            @include('layouts.navbars.auth.nav')
            <div class="w-full px-6 py-6 mx-auto">
                
                <!-- Page Header -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <div class="relative group">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-3 shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                                    <i class="fas fa-brain text-white text-sm"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-blue-600 group-hover:to-purple-600 group-hover:bg-clip-text transition-all duration-300">AI Gainer Engine</h1>
                                <p class="text-gray-500 text-xs mt-1">Potential Top Gainer — technicals and sentiment aligned for upward movement</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
                        <!-- Search Form -->
                        <form method="GET" action="{{ route('signals.index') }}" class="relative w-full sm:w-48 group">
                            @if(request('filter'))
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            @endif
                            @if(request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif
                            @if(request('direction'))
                                <input type="hidden" name="direction" value="{{ request('direction') }}">
                            @endif
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-xs group-hover:text-blue-500 transition-colors duration-300"></i>
                            </div>
                            <input id="symbol" name="symbol" type="text" 
                                   class="w-full bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition-all duration-300 shadow-sm hover:shadow-md group-hover:border-blue-300" 
                                   value="{{ request('symbol') }}" placeholder="Search symbol...">
                            @if(request('symbol'))
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <a href="{{ route('signals.index', array_merge(request()->except('symbol'), ['page' => 1])) }}" 
                                   class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-times text-xs"></i>
                                </a>
                            </div>
                            @endif
                        </form>
                        
                        <!-- Refresh Button -->
                        <form action="{{ route('signals.refresh') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-gradient-to-br from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-medium py-2.5 px-4 rounded-xl flex items-center text-xs transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0 group">
                                <i class="fas fa-sync-alt mr-2 text-xs group-hover:rotate-180 transition-transform duration-500"></i>
                                Refresh Data
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-gradient-to-r from-emerald-50 to-green-50 backdrop-blur-sm border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center shadow-lg hover:shadow-xl transition-all duration-300" role="alert">
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
                    <div class="mb-6 bg-gradient-to-r from-rose-50 to-red-50 backdrop-blur-sm border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm flex items-center shadow-lg hover:shadow-xl transition-all duration-300" role="alert">
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

                <!-- Signal Count Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                    <!-- Total Signals -->
                    <a href="{{ route('signals.index', request()->except(['filter', 'page'])) }}" 
                       class="bg-white/95 backdrop-blur-sm border {{ !request('filter') ? 'border-blue-300 shadow-xl' : 'border-blue-100/80' }} rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-blue-200/60 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors duration-300">{{ $totalSignals }}</div>
                                <div class="text-sm font-semibold text-gray-600 group-hover:text-blue-500 transition-colors duration-300 mt-1">Total Signals</div>
                                <div class="text-xs text-gray-400 font-medium group-hover:text-blue-400 transition-colors duration-300 mt-2">Live updates</div>
                            </div>
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                    <i class="fas fa-satellite text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- Filter Score -->
                    <a href="{{ route('signals.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'score'])) }}" 
                       class="bg-white/95 backdrop-blur-sm border {{ request('filter') == 'score' ? 'border-emerald-300 shadow-xl' : 'border-emerald-100/80' }} rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-emerald-200/60 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-gray-800 group-hover:text-emerald-600 transition-colors duration-300">{{ $filteredSignalsCount['score'] }}</div>
                                <div class="text-sm font-semibold text-gray-600 group-hover:text-emerald-500 transition-colors duration-300 mt-1">Filter Score</div>
                                <div class="text-xs text-gray-400 font-medium group-hover:text-emerald-400 transition-colors duration-300 mt-2">Score > 70</div>
                            </div>
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                    <i class="fas fa-filter text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- High Confidence -->
                    <a href="{{ route('signals.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'high_confidence'])) }}" 
                       class="bg-white/95 backdrop-blur-sm border {{ request('filter') == 'high_confidence' ? 'border-amber-300 shadow-xl' : 'border-amber-100/80' }} rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-amber-200/60 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-gray-800 group-hover:text-amber-600 transition-colors duration-300">{{ $filteredSignalsCount['high_confidence'] }}</div>
                                <div class="text-sm font-semibold text-gray-600 group-hover:text-amber-500 transition-colors duration-300 mt-1">High Confidence</div>
                                <div class="text-xs text-gray-400 font-medium group-hover:text-amber-400 transition-colors duration-300 mt-2">Premium picks</div>
                            </div>
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                    <i class="fas fa-star text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- Low Risk -->
                    <a href="{{ route('signals.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'low_risk'])) }}" 
                       class="bg-white/95 backdrop-blur-sm border {{ request('filter') == 'low_risk' ? 'border-teal-300 shadow-xl' : 'border-teal-100/80' }} rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-teal-200/60 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-2xl font-bold text-gray-800 group-hover:text-teal-600 transition-colors duration-300">{{ $filteredSignalsCount['low_risk'] }}</div>
                                <div class="text-sm font-semibold text-gray-600 group-hover:text-teal-500 transition-colors duration-300 mt-1">Low Risk</div>
                                <div class="text-xs text-gray-400 font-medium group-hover:text-teal-400 transition-colors duration-300 mt-2">Secure assets</div>
                            </div>
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                    <i class="fas fa-shield-alt text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Active Filters Display -->
                @if(request()->hasAny(['filter', 'symbol']))
                <div class="mb-6 bg-gradient-to-r from-blue-50/80 to-indigo-50/80 backdrop-blur-sm border border-blue-100 rounded-xl p-4 shadow-lg transition-all duration-500">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="text-sm font-semibold text-blue-800">Active Filters:</span>
                            @if(request('filter') == 'score')
                                <a href="{{ route('signals.index', request()->except(['filter', 'page'])) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-emerald-500 to-green-500 text-white text-xs rounded-full font-medium hover:from-emerald-600 hover:to-green-600 transition-all duration-300">
                                    <i class="fas fa-filter mr-1 text-xs"></i>
                                    Score > 70
                                    <i class="fas fa-times ml-2 text-xs"></i>
                                </a>
                            @endif
                            @if(request('filter') == 'high_confidence')
                                <a href="{{ route('signals.index', request()->except(['filter', 'page'])) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs rounded-full font-medium hover:from-amber-600 hover:to-orange-600 transition-all duration-300">
                                    <i class="fas fa-star mr-1 text-xs"></i>
                                    High Confidence
                                    <i class="fas fa-times ml-2 text-xs"></i>
                                </a>
                            @endif
                            @if(request('filter') == 'low_risk')
                                <a href="{{ route('signals.index', request()->except(['filter', 'page'])) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-teal-500 to-emerald-500 text-white text-xs rounded-full font-medium hover:from-teal-600 hover:to-emerald-600 transition-all duration-300">
                                    <i class="fas fa-shield-alt mr-1 text-xs"></i>
                                    Low Risk
                                    <i class="fas fa-times ml-2 text-xs"></i>
                                </a>
                            @endif
                            @if(request('symbol'))
                                <a href="{{ route('signals.index', request()->except(['symbol', 'page'])) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs rounded-full font-medium hover:from-purple-600 hover:to-pink-600 transition-all duration-300">
                                    <i class="fas fa-search mr-1 text-xs"></i>
                                    Symbol: {{ request('symbol') }}
                                    <i class="fas fa-times ml-2 text-xs"></i>
                                </a>
                            @endif
                        </div>
                        @if(request()->hasAny(['filter', 'symbol']))
                        <a href="{{ route('signals.index') }}" 
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Clear All
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Top Sectors -->
                <div class="mb-6 bg-gradient-to-r from-amber-50/80 to-orange-50/80 backdrop-blur-sm border border-amber-100 rounded-xl p-4 shadow-lg hover:shadow-xl transition-all duration-500 group hover:scale-[1.02] cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center mr-3 shadow-md group-hover:scale-110 group-hover:rotate-12 transition-all duration-500">
                                <i class="fas fa-fire text-white text-xs"></i>
                            </div>
                            <div>
                                <span class="text-sm font-semibold text-amber-800 group-hover:text-amber-900 transition-colors duration-300">Top Sectors Today</span>
                                <div class="text-xs text-amber-700 mt-1 font-medium whitespace-normal break-words group-hover:text-amber-800 transition-colors duration-300">
                                    @if(count($topSectors) > 0)
                                        @php
                                            $sectorsText = implode(' • ', array_slice($topSectors, 0, 4));
                                            if (count($topSectors) > 4) {
                                                $sectorsText .= ' • ...';
                                            }
                                        @endphp
                                        {{ $sectorsText }}
                                    @else
                                        <span class="text-amber-600">Loading sectors data...</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-amber-500 group-hover:text-amber-600 group-hover:translate-x-1 transition-all duration-300">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Signals Table -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-500">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-gray-100/80">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 hover:text-transparent hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 hover:bg-clip-text transition-all duration-300 cursor-pointer">Trading Signals</h3>
                                <p class="text-gray-500 text-xs mt-1">Real-time market insights</p>
                            </div>
                            <div class="text-xs text-gray-600 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-lg border border-gray-200 font-medium shadow-sm hover:shadow-md transition-all duration-300">
                                <span class="text-blue-600 font-semibold">{{ $signals->total() }}</span> signals found
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full text-sm">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100/80 backdrop-blur-sm">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <a href="{{ route('signals.index', array_merge(request()->except(['sort', 'direction', 'page']), [
                                            'sort' => 'symbol', 
                                            'direction' => request('sort') == 'symbol' && request('direction') == 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-hashtag text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Symbol</span>
                                            @if(request('sort') == 'symbol')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs ml-2 text-blue-500 animate-pulse"></i>
                                            @else
                                                <i class="fas fa-sort text-xs ml-2 text-gray-400 group-hover:text-gray-600 transition-colors duration-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <a href="{{ route('signals.index', array_merge(request()->except(['sort', 'direction', 'page']), [
                                            'sort' => 'enhanced_score', 
                                            'direction' => request('sort') == 'enhanced_score' && request('direction') == 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-chart-line text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Score</span>
                                            @if(request('sort') == 'enhanced_score')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs ml-2 text-blue-500 animate-pulse"></i>
                                            @else
                                                <i class="fas fa-sort text-xs ml-2 text-gray-400 group-hover:text-gray-600 transition-colors duration-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                        <i class="fas fa-shield-alt text-gray-400 text-xs mr-2"></i>
                                        Confidence
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                        <i class="fas fa-dollar-sign text-gray-400 text-xs mr-2"></i>
                                        Price
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <a href="{{ route('signals.index', array_merge(request()->except(['sort', 'direction', 'page']), [
                                            'sort' => 'first_detection_time', 
                                            'direction' => request('sort') == 'first_detection_time' && request('direction') == 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-clock text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>First Detected</span>
                                            @if(request('sort') == 'first_detection_time')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-xs ml-2 text-blue-500 animate-pulse"></i>
                                            @else
                                                <i class="fas fa-sort text-xs ml-2 text-gray-400 group-hover:text-gray-600 transition-colors duration-300"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                        <i class="fas fa-chart-bar text-gray-400 text-xs mr-2"></i>
                                        Volume
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                        <i class="fas fa-layer-group text-gray-400 text-xs mr-2"></i>
                                        Sector
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100">
                                        <i class="fas fa-arrow-right text-gray-400 text-xs mr-2"></i>
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($signals as $signal)
                                @php
                                    $isTopSector = in_array($signal->category, $topSectors);
                                @endphp
                                <tr class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-purple-50/50 transition-all duration-300 group cursor-pointer transform hover:scale-[1.01]">
                                    <!-- Symbol -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition-colors duration-300">{{ $signal->symbol }}</div>
                                            @if($isTopSector)
                                                <span class="ml-2 px-2 py-1 bg-gradient-to-r from-amber-400 to-orange-500 text-white text-xs rounded-full flex items-center shadow-sm group-hover:shadow-md group-hover:scale-105 transition-all duration-300">
                                                    <i class="fas fa-bolt text-xs mr-1"></i>
                                                    Hot
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-gray-500 text-xs mt-1 group-hover:text-gray-600 transition-colors duration-300">{{ Str::limit($signal->name, 18) }}</div>
                                    </td>
                                    
                                    <!-- Score -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $score = $signal->enhanced_score;
                                            $scoreColor = $score >= 8 ? 'from-emerald-500 to-green-500' : 
                                                         ($score >= 6 ? 'from-blue-500 to-cyan-500' : 
                                                         ($score >= 4 ? 'from-amber-500 to-yellow-500' : 'from-rose-500 to-pink-500'));
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gradient-to-r {{ $scoreColor }} text-white shadow-sm group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
                                            {{ number_format($signal->enhanced_score, 1) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Confidence -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-12 bg-gray-200 rounded-full h-1.5 shadow-inner group-hover:w-14 transition-all duration-500">
                                                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-1.5 rounded-full transition-all duration-1000 ease-out" style="width: {{ $signal->smart_confidence }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-700 min-w-[35px] group-hover:text-blue-600 transition-colors duration-300">{{ $signal->smart_confidence }}%</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Price -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 group-hover:text-green-600 transition-colors duration-300">
                                            ${{ number_format($signal->current_price, $signal->current_price > 1 ? 2 : 4) }}
                                        </div>
                                    </td>
                                    
                                    <!-- First Detection -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs text-gray-900 font-semibold group-hover:text-gray-700 transition-colors duration-300">
                                            {{ $signal->first_detection_time->format('M j') }}
                                        </div>
                                        <div class="text-xs text-gray-500 group-hover:text-gray-600 transition-colors duration-300">
                                            {{ $signal->first_detection_time->format('H:i') }}
                                        </div>
                                    </td>
                                    
                                    <!-- Volume -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $volumeRatio = $signal->volume_spike_ratio;
                                            $volumeColor = $volumeRatio > 2 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 
                                                          ($volumeRatio > 1.5 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 
                                                          ($volumeRatio > 1 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-800 border border-gray-200'));
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold {{ $volumeColor }} group-hover:scale-105 transition-all duration-300">
                                            {{ number_format($signal->volume_spike_ratio, 1) }}x
                                        </span>
                                    </td>
                                    
                                    <!-- Sector -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-xs font-medium {{ $isTopSector ? 'text-amber-700 bg-amber-50 px-2 py-1 rounded-lg border border-amber-200 group-hover:bg-amber-100 group-hover:border-amber-300' : 'text-gray-700 bg-gray-50 px-2 py-1 rounded-lg border border-gray-200 group-hover:bg-gray-100' }} transition-all duration-300">
                                                {{ Str::limit($signal->category ?? 'Unknown', 12) }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Action -->
                                    <td class="px-4 py-3 whitespace-nowrap text-xs font-semibold">
                                        <a href="{{ route('signals.show', $signal->symbol) }}" 
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

                    <!-- Pagination -->
                    <div class="px-5 py-3 border-t border-gray-100 bg-gradient-to-r from-gray-50/80 to-gray-100/80">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-xs text-gray-600 font-medium">
                                Showing <span class="text-gray-900 font-semibold">{{ $signals->firstItem() }}</span> to 
                                <span class="text-gray-900 font-semibold">{{ $signals->lastItem() }}</span> of 
                                <span class="text-gray-900 font-semibold">{{ $signals->total() }}</span>
                            </div>
                            <div class="flex items-center bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                                {{ $signals->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>

                @include('layouts.footers.auth.footer')
            </div>
        </main>
    @else
        @include('layouts.navbars.guest.nav')
        <div class="w-full px-6 py-6 mx-auto">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800">Trading Signals</h1>
                <p class="text-gray-600 mt-2">Please login to view trading signals</p>
            </div>
        </div>
        @include('layouts.footers.guest.footer')
    @endauth

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced search functionality
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

            // Add click effects to filter cards
            const cards = document.querySelectorAll('.grid > a');
            cards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Add visual feedback
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        });

        // Simple filter functions for active filter badges
        function removeSymbolFilter() {
            window.location.href = "{{ route('signals.index', request()->except(['symbol', 'page'])) }}";
        }

        function removeFilter(filterType) {
            window.location.href = "{{ route('signals.index', request()->except(['filter', 'page'])) }}";
        }

        function clearAllFilters() {
            window.location.href = "{{ route('signals.index') }}";
        }
    </script>
    @endpush
</x-layouts.base>