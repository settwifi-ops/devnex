<div>
    <!-- row 1 - Metrics Cards -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Bull Dominance Card -->
        <a href="{{ url('/dashboard/market/regime/bull') }}" class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4 transition-transform duration-300 hover:scale-105">
            <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border cursor-pointer hover:shadow-xl transition-all duration-300">
                <div class="flex-auto p-4">
                    <div class="flex flex-row -mx-3">
                        <div class="flex-none w-2/3 max-w-full px-3">
                            <div>
                                <p class="mb-0 font-sans font-semibold leading-normal text-size-sm">Bull Dominance</p>
                                <h5 class="mb-0 font-bold">
                                    {{ $regimeDistribution['bull']['percentage'] ?? 0 }}%
                                </h5>
                                <p class="mb-0 text-sm">
                                    @if(($regimeDistribution['bull']['percentage'] ?? 0) > 60)
                                    <span class="font-weight-bolder text-lime-500"><i class="fas fa-fire-alt mr-1"></i>Strong</span>
                                    @elseif(($regimeDistribution['bull']['percentage'] ?? 0) > 40)
                                    <span class="font-weight-bolder text-yellow-500"><i class="fas fa-smile mr-1"></i>Moderate</span>
                                    @else
                                    <span class="font-weight-bolder text-red-500"><i class="fas fa-meh mr-1"></i>Weak</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="px-3 text-right basis-1/3">
                            <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-to-tr from-green-500 to-emerald-400">
                                <i class="fas fa-arrow-up text-size-lg relative top-3.5 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>

        <!-- Positive Gain Card -->
        <a href="{{ route('performance.index') }}" class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4 transition-transform duration-300 hover:scale-105">
            <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border cursor-pointer hover:shadow-xl transition-all duration-300">
                <div class="flex-auto p-4">
                    <div class="flex flex-row -mx-3">
                        <div class="flex-none w-2/3 max-w-full px-3">
                            <div>
                                <p class="mb-0 font-sans font-semibold leading-normal text-size-sm">Positive Gain</p>
                                <h5 class="mb-0 font-bold">
                                    {{ $positiveGain ?? 0 }}
                                    <span class="leading-normal text-size-sm font-weight-bolder text-lime-500">Assets</span>
                                </h5>
                                <p class="text-xs text-gray-500 mt-1">
                                    @php
                                        $totalAssets = App\Models\Performance::count();
                                        $positivePercentage = $totalAssets > 0 ? round(($positiveGain / $totalAssets) * 100, 1) : 0;
                                    @endphp
                                    
                                    @if($positivePercentage >= 70)
                                    <span class="text-green-600"><i class="fas fa-rocket mr-1"></i>Excellent</span>
                                    @elseif($positivePercentage >= 50)
                                    <span class="text-yellow-600"><i class="fas fa-chart-line mr-1"></i>Good</span>
                                    @else
                                    <span class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>Low</span>
                                    @endif
                                    <span class="ml-1">({{ $positivePercentage }}%)</span>
                                </p>
                            </div>
                        </div>
                        <div class="px-3 text-right basis-1/3">
                            <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-to-tr from-blue-500 to-cyan-400">
                                <i class="fas fa-chart-line text-size-lg relative top-3.5 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>

        <!-- Bear Dominance Card -->
        <a href="{{ url('/dashboard/market/regime/bear') }}" class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4 transition-transform duration-300 hover:scale-105">
            <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border cursor-pointer hover:shadow-xl transition-all duration-300">
                <div class="flex-auto p-4">
                    <div class="flex flex-row -mx-3">
                        <div class="flex-none w-2/3 max-w-full px-3">
                            <div>
                                <p class="mb-0 font-sans font-semibold leading-normal text-size-sm">Bear Dominance</p>
                                <h5 class="mb-0 font-bold">
                                    {{ $regimeDistribution['bear']['percentage'] ?? 0 }}%
                                </h5>
                                <p class="mb-0 text-sm">
                                    @if(($regimeDistribution['bear']['percentage'] ?? 0) > 60)
                                    <span class="font-weight-bolder text-red-500"><i class="fas fa-angle-double-up mr-1"></i></i>Strong</span>
                                    @elseif(($regimeDistribution['bear']['percentage'] ?? 0) > 40)
                                    <span class="font-weight-bolder text-yellow-500"><i class="fas fa-chart-line mr-1"></i></i>Moderate</span>
                                    @else
                                    <span class="font-weight-bolder text-lime-500"><i class="fas fa-angle-double-down mr-1"></i>Weak</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="px-3 text-right basis-1/3">
                            <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-to-tr from-red-500 to-orange-400">
                                <i class="fas fa-arrow-down text-size-lg relative top-3.5 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>


        <!-- High Confidence Signals Card -->
        <a href="{{ route('signals.index') }}" class="w-full max-w-full px-3 mb-6 sm:w-1/2 sm:flex-none xl:mb-0 xl:w-1/4 transition-transform duration-300 hover:scale-105">
            <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border cursor-pointer hover:shadow-xl transition-all duration-300">
                <div class="flex-auto p-4">
                    <div class="flex flex-row -mx-3">
                        <div class="flex-none w-2/3 max-w-full px-3">
                            <div>
                                <p class="mb-0 font-sans font-semibold leading-normal text-size-sm">High Confidence</p>
                                <h5 class="mb-0 font-bold">
                                    {{ $highConfidence ?? 0 }}
                                    <span class="leading-normal text-size-sm font-weight-bolder text-lime-500">Signals</span>
                                </h5>
                                <p class="text-xs text-gray-500 mt-1">
                                    @if(($highConfidence ?? 0) > 15)
                                    <span class="text-green-600"><i class="fas fa-rocket mr-1"></i>Excellent</span>
                                    @elseif(($highConfidence ?? 0) > 8)
                                    <span class="text-yellow-600"><i class="fas fa-chart-line mr-1"></i>Good</span>
                                    @else
                                    <span class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>Low</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="px-3 text-right basis-1/3">
                            <div class="inline-block w-12 h-12 text-center rounded-lg bg-gradient-to-tr from-purple-500 to-pink-400">
                                <i class="fas fa-bullseye text-size-lg relative top-3.5 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- cards row 2 -->
    <div class="flex flex-wrap mt-6 -mx-3">
        <!-- Kolom Kiri - Multiple Cards Vertikal -->
        <div class="w-full px-3 mb-6 lg:mb-0 lg:w-7/12 lg:flex-none">
            <div class="space-y-6">
                
                <!-- Card 2 Kiri: Actionable Research Insights (Versi Indah) -->
                <div class="relative flex flex-col min-w-0 break-words bg-gradient-to-br from-white to-gray-50 shadow-soft-xl rounded-2xl bg-clip-border border border-gray-100">
                    <div class="flex-auto p-6">
                        <!-- Header dengan gradient -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg">
                                    <i class="fas fa-lightbulb text-white text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <h2 class="text-xl font-bold text-gray-800">Actionable Research Insights</h2>
                                    <p class="text-sm text-gray-600">Real-time market intelligence</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-bolt mr-1"></i>
                                    {{ count($researchInsights) }} Insights
                                </span>
                            </div>
                        </div>
                        
                        <!-- Insights Grid yang Indah -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($researchInsights as $index => $insight)
                            <div class="relative group">
                                <div class="absolute -inset-0.5 bg-gradient-to-r 
                                    @if($insight['type'] === 'opportunity') from-green-400 to-emerald-400
                                    @elseif($insight['type'] === 'warning') from-yellow-400 to-orange-400
                                    @elseif($insight['type'] === 'critical') from-red-400 to-pink-400
                                    @else from-blue-400 to-cyan-400 @endif 
                                    rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-300"></div>
                                
                                <div class="relative bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-all duration-300 hover:scale-105">
                                    <!-- Header dengan icon dan title -->
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center">
                                            <div class="p-2 rounded-lg 
                                                @if($insight['type'] === 'opportunity') bg-green-100 text-green-600
                                                @elseif($insight['type'] === 'warning') bg-yellow-100 text-yellow-600
                                                @elseif($insight['type'] === 'critical') bg-red-100 text-red-600
                                                @else bg-blue-100 text-blue-600 @endif">
                                                @if($insight['type'] === 'opportunity')
                                                <i class="fas fa-chart-line text-sm"></i>
                                                @elseif($insight['type'] === 'warning')
                                                <i class="fas fa-exclamation-triangle text-sm"></i>
                                                @elseif($insight['type'] === 'critical')
                                                <i class="fas fa-fire text-sm"></i>
                                                @else
                                                <i class="fas fa-info-circle text-sm"></i>
                                                @endif
                                            </div>
                                            <h3 class="ml-3 font-semibold text-gray-800 text-sm leading-tight">{{ $insight['title'] }}</h3>
                                        </div>
                                        <div class="flex space-x-1">
                                            <!-- Confidence Badge -->
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                @if($insight['confidence'] >= 80) bg-green-100 text-green-800
                                                @elseif($insight['confidence'] >= 60) bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                <i class="fas fa-bullseye mr-1"></i>
                                                {{ $insight['confidence'] }}%
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Description -->
                                    <p class="text-gray-600 text-xs leading-relaxed mb-3 line-clamp-2">
                                        {{ $insight['description'] }}
                                    </p>
                                    
                                    <!-- Footer dengan impact dan action -->
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                        <span class="inline-flex items-center text-xs font-medium 
                                            @if($insight['impact'] === 'high') text-red-600
                                            @elseif($insight['impact'] === 'medium') text-yellow-600
                                            @else text-green-600 @endif">
                                            <i class="fas fa-arrow-trend-up mr-1"></i>
                                            {{ ucfirst($insight['impact']) }} Impact
                                        </span>
                                        
                                        <div class="flex space-x-1">
                                            @if($insight['type'] === 'opportunity')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-50 text-green-700 border border-green-200">
                                                <i class="fas fa-play mr-1"></i>Act
                                            </span>
                                            @elseif($insight['type'] === 'warning')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-50 text-yellow-700 border border-yellow-200">
                                                <i class="fas fa-shield mr-1"></i>Defend
                                            </span>
                                            @elseif($insight['type'] === 'critical')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-50 text-red-700 border border-red-200">
                                                <i class="fas fa-bell mr-1"></i>Alert
                                            </span>
                                            @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fas fa-eye mr-1"></i>Monitor
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Empty State -->
                        @if(count($researchInsights) === 0)
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full mb-4">
                                <i class="fas fa-search text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">No Insights Available</h3>
                            <p class="text-gray-500 max-w-sm mx-auto">
                                Market conditions are stable. Insights will appear when significant opportunities or risks are detected.
                            </p>
                        </div>
                        @endif
                        
                        <!-- Footer dengan refresh info -->
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    Updated just now
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                    Live data
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative flex flex-col min-w-0 break-words
                    bg-gradient-to-br from-blue-50 to-indigo-50 shadow-soft-xl 
                    rounded-2xl bg-clip-border border border-blue-100
                    hover:shadow-xl transition-all duration-300 ease-in-out
                    transform hover:-translate-y-1">

                    <!-- Animated background elements -->
                    <div class="absolute -top-4 -right-4 w-20 h-20 bg-blue-200 rounded-full opacity-20 animate-pulse-slow"></div>
                    <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-indigo-200 rounded-full opacity-20 animate-pulse-slow" style="animation-delay: 1.5s"></div>

                    <div class="flex-auto p-6 relative z-10">
                        
                        <!-- Header with enhanced styling -->
                        <div class="flex items-center mb-6">
                            <div class="p-3 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg 
                                hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                <i class="fas fa-wave-square text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-bold text-gray-800">Correlation Insights</h2>
                                <p class="text-sm text-gray-600 mt-1">Market relationships & diversification</p>
                            </div>
                        </div>

                        <!-- Positive Correlation with enhanced items -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-blue-700 flex items-center">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></span>
                                    Strong Positive Correlation
                                </h3>
                            </div>

                            <div class="space-y-2">
                                @forelse($correlationTopPositive as $index => $pos)
                                    <div class="flex justify-between items-center text-sm 
                                        bg-white p-3 rounded-xl border border-blue-100 shadow-sm
                                        hover:shadow-md hover:border-blue-300 transition-all duration-200
                                        transform hover:-translate-y-0.5 animate-slide-up"
                                        style="animation-delay: {{ $index * 0.1 }}s">
                                        <span class="font-medium text-gray-700">{{ $pos['pair'] }}</span>
                                        <span class="font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-lg 
                                            border border-blue-200 transition-colors duration-200
                                            hover:bg-blue-100">
                                            {{ $pos['value'] }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <p class="text-xs text-gray-500 bg-white p-3 rounded-lg border border-gray-200">
                                            No data available
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Negative Correlation with enhanced items -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-red-700 flex items-center">
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                                    Best Diversification Picks
                                </h3>
                            </div>

                            <div class="space-y-2">
                                @forelse($correlationTopNegative as $index => $neg)
                                    <div class="flex justify-between items-center text-sm 
                                        bg-white p-3 rounded-xl border border-red-100 shadow-sm
                                        hover:shadow-md hover:border-red-300 transition-all duration-200
                                        transform hover:-translate-y-0.5 animate-slide-up"
                                        style="animation-delay: {{ ($index + count($correlationTopPositive)) * 0.1 }}s">
                                        <span class="font-medium text-gray-700">{{ $neg['pair'] }}</span>
                                        <span class="font-bold text-red-700 bg-red-50 px-3 py-1 rounded-lg 
                                            border border-red-200 transition-colors duration-200
                                            hover:bg-red-100">
                                            {{ $neg['value'] }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <p class="text-xs text-gray-500 bg-white p-3 rounded-lg border border-gray-200">
                                            No data available
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

                <style>
                .animate-pulse-slow {
                    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
                }

                .animate-slide-up {
                    animation: slideUp 0.5s ease-out forwards;
                    opacity: 0;
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
                    0%, 100% {
                        opacity: 0.2;
                    }
                    50% {
                        opacity: 0.4;
                    }
                }
                </style>
            </div>
        </div>

        <div class="w-full px-3 lg:w-5/12 lg:flex-none">
            <!-- Card 2 Kanan: Top Signals Carousel (Panjang ke bawah) -->
            <div class="relative flex flex-col min-w-0 break-words bg-white/90 backdrop-blur-sm shadow-soft-xl rounded-2xl bg-clip-border border border-gray-200/80 hover:border-indigo-200/60 transition-all duration-500 group top-signal-card">
                <!-- Background Glow Effect -->
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500/5 to-purple-600/5 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-all duration-700"></div>
                
                <!-- Animated Border -->
                <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-indigo-500/20 to-purple-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    <div class="absolute inset-[1.5px] rounded-2xl bg-white/90 backdrop-blur-sm"></div>
                </div>

                <div class="flex-auto p-6 relative z-10">
                    <!-- Header Section -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="relative">
                                <div class="p-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-500">
                                    <i class="fas fa-robot text-white text-lg"></i>
                                </div>
                                <!-- Sparkle Effect -->
                                <div class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full opacity-0 group-hover:opacity-100 group-hover:animate-ping transition-opacity duration-500">
                                    <i class="fas fa-sparkle text-white text-xs absolute inset-0 flex items-center justify-center"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-bold text-gray-800 group-hover:text-gray-900 transition-colors duration-300">AI Top Decisions</h3>
                                <p class="text-sm text-gray-600 group-hover:text-gray-700 transition-colors duration-300">Highest Confidence Signals</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button wire:click="refreshTopSignals" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-300" title="Refresh AI Decisions">
                                <i class="fas fa-sync-alt text-sm"></i>
                            </button>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 group-hover:bg-indigo-200 group-hover:text-indigo-900 transition-all duration-300 shadow-sm">
                                @if(count($topSignals) > 0)
                                    {{ $currentTopSignalIndex + 1 }}/{{ count($topSignals) }}
                                @else
                                    0/0
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <!-- Signal Carousel -->
                    @if(count($topSignals) > 0)
                    <div class="relative">
                        <!-- Current Signal Card -->
                        <a href="{{ route('portfolio') }}" class="block group signal-item">
                            <!-- Ripple Effect Container -->
                            <div class="ripple-container absolute inset-0 overflow-hidden rounded-xl pointer-events-none"></div>
                            
                            <div class="bg-white/95 backdrop-blur-sm rounded-xl p-6 border border-gray-200/60 hover:border-indigo-200 hover:shadow-lg transition-all duration-300 group-hover:scale-[1.02] relative overflow-hidden">
                                <!-- Shine Effect -->
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <!-- Header dengan symbol dan confidence -->
                                <div class="flex items-center justify-between mb-4 relative z-10">
                                    <div class="flex items-center">
                                        <span class="font-bold text-gray-800 text-xl group-hover:text-indigo-600 transition-colors duration-300 transform group-hover:scale-105">
                                            {{ $topSignals[$currentTopSignalIndex]['symbol'] }}
                                        </span>
                                        <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium shadow-sm transition-all duration-300 transform group-hover:scale-105
                                            @if($topSignals[$currentTopSignalIndex]['action'] === 'BUY') bg-green-100 text-green-800 group-hover:bg-green-200 border border-green-200
                                            @else bg-red-100 text-red-800 group-hover:bg-red-200 border border-red-200 @endif">
                                            <i class="fas fa-{{ $topSignals[$currentTopSignalIndex]['action'] === 'BUY' ? 'arrow-up' : 'arrow-down' }} mr-1.5 text-xs"></i>
                                            {{ $topSignals[$currentTopSignalIndex]['action'] }}
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium shadow-sm transition-all duration-300 transform group-hover:scale-105
                                        @if($topSignals[$currentTopSignalIndex]['confidence'] >= 80) bg-green-100 text-green-800 group-hover:bg-green-200 border border-green-200
                                        @elseif($topSignals[$currentTopSignalIndex]['confidence'] >= 60) bg-yellow-100 text-yellow-800 group-hover:bg-yellow-200 border border-yellow-200
                                        @else bg-red-100 text-red-800 group-hover:bg-red-200 border border-red-200 @endif">
                                        <i class="fas fa-brain mr-1.5 text-xs"></i>
                                        AI: {{ $topSignals[$currentTopSignalIndex]['confidence'] }}%
                                    </span>
                                </div>
                                
                                <!-- Price and Time Information -->
                                <div class="mb-4 relative z-10">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="text-gray-600">Entry Price:</span>
                                        <span class="font-semibold text-gray-800">${{ number_format($topSignals[$currentTopSignalIndex]['price'], 4) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Decision Time:</span>
                                        <span class="text-gray-700" title="{{ $topSignals[$currentTopSignalIndex]['decision_time'] }}">
                                            {{ $topSignals[$currentTopSignalIndex]['time_ago'] }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Action Badge -->
                                <div class="mb-4 relative z-10">
                                    <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium shadow-sm transition-all duration-300 transform group-hover:scale-105
                                        @if($topSignals[$currentTopSignalIndex]['action'] === 'BUY') bg-green-50 text-green-700 border border-green-200 group-hover:bg-green-100 group-hover:border-green-300
                                        @else bg-red-50 text-red-700 border border-red-200 group-hover:bg-red-100 group-hover:border-red-300 @endif">
                                        <i class="fas fa-chart-line mr-2 @if($topSignals[$currentTopSignalIndex]['action'] === 'BUY') text-green-600 @else text-red-600 @endif"></i>
                                        {{ $topSignals[$currentTopSignalIndex]['trend_power'] }}
                                    </span>
                                </div>
                                
                                <!-- Summary / Explanation -->
                                <div class="mb-6 relative z-10">
                                    <p class="text-gray-700 text-base leading-relaxed group-hover:text-gray-800 transition-colors duration-300">
                                        {{ $topSignals[$currentTopSignalIndex]['summary'] }}
                                    </p>
                                </div>
                                
                                <!-- Confidence Indicator Bar -->
                                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 rounded-b-xl overflow-hidden">
                                    <div class="h-full transition-all duration-1000 ease-out 
                                        @if($topSignals[$currentTopSignalIndex]['confidence'] >= 80) bg-green-500
                                        @elseif($topSignals[$currentTopSignalIndex]['confidence'] >= 60) bg-yellow-500
                                        @else bg-red-500 @endif"
                                        style="width: {{ $topSignals[$currentTopSignalIndex]['confidence'] }}%">
                                    </div>
                                </div>

                                <!-- Fallback Badge -->
                                @if(isset($topSignals[$currentTopSignalIndex]['is_fallback']) && $topSignals[$currentTopSignalIndex]['is_fallback'])
                                <div class="absolute top-2 right-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Fallback
                                    </span>
                                </div>
                                @endif
                            </div>
                        </a>

                        <!-- Navigation Dots -->
                        <div class="flex justify-center mt-6 space-x-2">
                            @foreach($topSignals as $index => $signal)
                            <button wire:click="goToTopSignal({{ $index }})" 
                                    class="w-2 h-2 rounded-full transition-all duration-300 
                                           @if($index === $currentTopSignalIndex) bg-indigo-600 w-6
                                           @else bg-gray-300 hover:bg-gray-400 @endif">
                            </button>
                            @endforeach
                        </div>

                        <!-- Navigation Arrows -->
                        <button wire:click="prevTopSignal" 
                                class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-4 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-full w-8 h-8 flex items-center justify-center shadow-lg hover:shadow-xl hover:scale-110 hover:bg-indigo-50 hover:border-indigo-200 transition-all duration-300 group/arrow">
                            <i class="fas fa-chevron-left text-gray-600 group-hover/arrow:text-indigo-600 text-sm"></i>
                        </button>
                        
                        <button wire:click="nextTopSignal" 
                                class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-4 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-full w-8 h-8 flex items-center justify-center shadow-lg hover:shadow-xl hover:scale-110 hover:bg-indigo-50 hover:border-indigo-200 transition-all duration-300 group/arrow">
                            <i class="fas fa-chevron-right text-gray-600 group-hover/arrow:text-indigo-600 text-sm"></i>
                        </button>
                    </div>
                    @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <div class="mb-4">
                            <i class="fas fa-robot text-gray-300 text-4xl"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-600 mb-2">No AI Decisions Available</h4>
                        <p class="text-gray-500 text-sm mb-4">AI decisions will appear here once generated</p>
                        <button wire:click="refreshTopSignals" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-300">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Refresh
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <style>
        /* Custom Styles untuk Top Signal Card */
        .top-signal-card {
            transform: translateY(0);
            transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .top-signal-card:hover {
            transform: translateY(-4px);
        }

        /* Ripple Effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0) 70%);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Shine Effect */
        .signal-item:hover .bg-gradient-to-r {
            animation: shine 1.5s ease-out;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }

        /* Smooth Scale Transitions */
        .transform {
            transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        /* Backdrop Blur Enhancement */
        .backdrop-blur-sm {
            backdrop-filter: blur(8px);
        }

        /* Glass Morphism Effect */
        .bg-white\/90 {
            background: rgba(255, 255, 255, 0.9);
        }

        /* Enhanced Shadow Effects */
        .shadow-soft-xl {
            box-shadow: 0 8px 26px -4px rgba(0, 0, 0, 0.15), 0 8px 9px -5px rgba(0, 0, 0, 0.06);
        }

        .hover\:shadow-lg:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Border Glow Effect */
        .border-gray-200\/80 {
            border-color: rgba(229, 231, 235, 0.8);
        }

        .hover\:border-indigo-200\/60:hover {
            border-color: rgba(199, 210, 254, 0.6);
        }

        /* Confidence Bar Animation */
        @keyframes confidenceFill {
            0% {
                width: 0%;
            }
            100% {
                width: var(--confidence-width);
            }
        }

        /* Pulse Animation for Active Dot */
        .animate-ping {
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes ping {
            75%, 100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            .top-signal-card {
                margin-top: 1rem;
            }
        }

        /* Smooth Color Transitions */
        .transition-colors {
            transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
        }

        /* Enhanced Button Interactions */
        .group\/nav:hover .group-hover\/nav\:text-indigo-600 {
            color: rgb(79 70 229);
        }

        /* Gradient Text Protection */
        .text-transparent {
            color: transparent;
        }

        /* Loading State Animation */
        @keyframes pulse-gentle {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .animate-pulse-gentle {
            animation: pulse-gentle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Top Signal Card Interactions
            const signalCard = document.querySelector('.top-signal-card');
            const signalItems = document.querySelectorAll('.signal-item');
            const navButtons = document.querySelectorAll('button[wire\\:click]');
            
            // Ripple Effect
            function createRipple(event, element) {
                const rippleContainer = element.querySelector('.ripple-container');
                if (!rippleContainer) return;
                
                const ripple = document.createElement('div');
                ripple.className = 'ripple';
                
                const rect = element.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = event.clientX - rect.left - size / 2;
                const y = event.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                
                rippleContainer.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            }
            
            // Add click effects to signal items
            signalItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    createRipple(e, item);
                });
            });
            
            // Add hover effects to navigation buttons
            navButtons.forEach(button => {
                if (!button.disabled) {
                    button.addEventListener('mouseenter', () => {
                        button.style.transform = 'scale(1.1)';
                    });
                    
                    button.addEventListener('mouseleave', () => {
                        button.style.transform = 'scale(1)';
                    });
                }
            });
            
            // Auto-rotate signals (optional feature)
            let autoRotateInterval;
            
            function startAutoRotate() {
                if (document.querySelector('.top-signal-card') && {{ count($topSignals) }} > 1) {
                    autoRotateInterval = setInterval(() => {
                        const nextBtn = document.querySelector('button[wire\\:click="nextTopSignal"]:not([disabled])');
                        if (nextBtn) {
                            nextBtn.click();
                        }
                    }, 8000); // Rotate every 8 seconds
                }
            }
            
            function stopAutoRotate() {
                if (autoRotateInterval) {
                    clearInterval(autoRotateInterval);
                }
            }
            
            // Start auto-rotate when card is visible
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        startAutoRotate();
                    } else {
                        stopAutoRotate();
                    }
                });
            });
            
            if (signalCard) {
                observer.observe(signalCard);
            }
            
            // Pause auto-rotate on hover
            signalCard.addEventListener('mouseenter', stopAutoRotate);
            signalCard.addEventListener('mouseleave', () => {
                if (document.querySelector('.top-signal-card') && document.querySelector('.top-signal-card').getBoundingClientRect().top < window.innerHeight) {
                    startAutoRotate();
                }
            });
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') {
                    const nextBtn = document.querySelector('button[wire\\:click="nextTopSignal"]:not([disabled])');
                    if (nextBtn) nextBtn.click();
                } else if (e.key === 'ArrowLeft') {
                    const prevBtn = document.querySelector('button[wire\\:click="prevTopSignal"]:not([disabled])');
                    if (prevBtn) prevBtn.click();
                }
            });
        });
        </script>
    </div>
</div>