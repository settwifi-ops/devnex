<x-layouts.base>
    @auth
        @include('layouts.navbars.auth.sidebar')
        <main class="ease-soft-in-out xl:ml-68.5 relative h-full max-h-screen rounded-xl transition-all duration-200">
            @include('layouts.navbars.auth.nav')
            <div class="w-full px-6 py-6 mx-auto">
                
                <!-- Header -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-chart-network text-white text-base"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Market Regime Analysis</h1>
                            <p class="text-gray-500 text-sm mt-1">Real-time market regime detection and asset performance analytics</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="text-sm text-gray-600 bg-white px-4 py-2 rounded-lg border border-gray-200 font-medium shadow-sm">
                            <i class="fas fa-calendar mr-2 text-blue-500"></i>
                            {{ now()->format('M j, Y') }}
                        </div>
                        <button class="bg-gradient-to-br from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-medium py-2 px-4 rounded-xl flex items-center text-sm transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 group">
                            <i class="fas fa-sync-alt mr-2 text-xs group-hover:rotate-180 transition-transform duration-500"></i>
                            Refresh
                        </button>
                    </div>
                </div>

                <!-- Critical Alerts -->
                @if($criticalAlerts->count() > 0)
                <div class="mb-8">
                    <div class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center mr-4 shadow-md">
                                    <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-red-800">Critical Market Alerts</h3>
                                    <p class="text-red-600 text-sm mt-1">{{ $criticalAlerts->count() }} unread critical alerts require attention</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                @foreach($criticalAlerts->take(3) as $alert)
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full border border-red-200">
                                    {{ $alert->symbol }}
                                </span>
                                @endforeach
                                @if($criticalAlerts->count() > 3)
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full border border-red-200">
                                    +{{ $criticalAlerts->count() - 3 }} more
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Market Overview Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
                    <!-- Left Column - Market Health & Regimes -->
                    <div class="xl:col-span-2 space-y-6">
                        <!-- Market Health Score -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-semibold text-gray-900">Market Health Overview</h2>
                                <div class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-lg border border-gray-200">
                                    Updated: {{ now()->format('H:i') }}
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Total Assets -->
                                <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100 group hover:border-blue-200 transition-all duration-300 cursor-pointer">
                                    <div class="text-3xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">
                                        {{ $currentRegimes->count() }}
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">Total Assets</div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 h-2 rounded-full transition-all duration-500" 
                                             style="width: {{ min(100, ($currentRegimes->count() / 200) * 100) }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Avg Confidence -->
                                <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border border-purple-100 group hover:border-purple-200 transition-all duration-300 cursor-pointer">
                                    <div class="text-3xl font-bold text-gray-900 group-hover:text-purple-600 transition-colors duration-300">
                                        {{ round($currentRegimes->avg('regime_confidence') * 100, 1) }}%
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">Avg Confidence</div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="bg-gradient-to-r from-purple-500 to-pink-400 h-2 rounded-full transition-all duration-500" 
                                             style="width: {{ $currentRegimes->avg('regime_confidence') * 100 }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Avg Volatility -->
                                <div class="text-center p-4 bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl border border-orange-100 group hover:border-orange-200 transition-all duration-300 cursor-pointer">
                                    <div class="text-3xl font-bold text-gray-900 group-hover:text-orange-600 transition-colors duration-300">
                                        {{ round($currentRegimes->avg('volatility_24h') * 100, 2) }}%
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">Avg Volatility</div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="bg-gradient-to-r from-orange-500 to-amber-400 h-2 rounded-full transition-all duration-500" 
                                             style="width: {{ min(100, $currentRegimes->avg('volatility_24h') * 500) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Regime Distribution -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-semibold text-gray-900">Market Regime Distribution</h2>
                                <div class="text-xs text-gray-500">Based on {{ $currentRegimes->count() }} assets</div>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @php
                                    // HITUNG LANGSUNG DARI DATABASE - SAMA DENGAN LIVEWIRE
                                    $regimeCounts = [
                                        'bull' => $currentRegimes->where('regime', 'bull')->count(),
                                        'bear' => $currentRegimes->where('regime', 'bear')->count(),
                                        'neutral' => $currentRegimes->where('regime', 'neutral')->count(),
                                        'volatile' => $currentRegimes->where('regime', 'volatile')->count(),
                                    ];
                                    $totalAssets = $currentRegimes->count() ?: 1;
                                @endphp
                                
                                @foreach(['bull', 'bear', 'neutral', 'volatile'] as $regime)
                                @php
                                    $count = $regimeCounts[$regime];
                                    $percentage = $totalAssets > 0 ? round(($count / $totalAssets) * 100, 1) : 0;
                                    $colors = [
                                        'bull' => ['bg' => 'from-green-500 to-emerald-600', 'text' => 'text-green-600', 'border' => 'border-green-200'],
                                        'bear' => ['bg' => 'from-red-500 to-rose-600', 'text' => 'text-red-600', 'border' => 'border-red-200'],
                                        'neutral' => ['bg' => 'from-yellow-500 to-amber-600', 'text' => 'text-yellow-600', 'border' => 'border-yellow-200'],
                                        'volatile' => ['bg' => 'from-purple-500 to-pink-600', 'text' => 'text-purple-600', 'border' => 'border-purple-200']
                                    ];
                                @endphp
                                <a href="{{ route('dashboard.regime-detail', $regime) }}" 
                                   class="bg-white rounded-xl border {{ $colors[$regime]['border'] }} p-4 text-center group hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br {{ $colors[$regime]['bg'] }} flex items-center justify-center mx-auto mb-3 shadow-md group-hover:scale-110 transition-all duration-300">
                                        <i class="fas fa-chart-line text-white text-sm"></i>
                                    </div>
                                    <div class="text-2xl font-bold {{ $colors[$regime]['text'] }} group-hover:scale-105 transition-transform duration-300">
                                        {{ $percentage }}%
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1 capitalize">{{ $regime }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $count }} assets</div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Research Insights -->
                    <div class="space-y-6">
                        <!-- Actionable Research Insights -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center mr-3 shadow-md">
                                        <i class="fas fa-lightbulb text-white text-sm"></i>
                                    </div>
                                    <h2 class="text-lg font-semibold text-gray-900">Actionable Research Insights</h2>
                                </div>
                                <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                                    AI Analysis
                                </div>
                            </div>
                            <div class="space-y-4 max-h-96 overflow-y-auto">
                                @php
                                    // GUNAKAN researchInsights YANG SAMA DENGAN LIVEWIRE
                                    $insights = $researchInsights;
                                @endphp
                                
                                @foreach($insights as $insight)
                                @php
                                    $typeColors = [
                                        'opportunity' => ['bg' => 'from-green-500/20 to-emerald-500/20', 'border' => 'border-green-200', 'icon' => 'fa-arrow-trend-up text-green-500'],
                                        'warning' => ['bg' => 'from-yellow-500/20 to-amber-500/20', 'border' => 'border-yellow-200', 'icon' => 'fa-exclamation-triangle text-yellow-500'],
                                        'critical' => ['bg' => 'from-red-500/20 to-rose-500/20', 'border' => 'border-red-200', 'icon' => 'fa-fire text-red-500'],
                                        'info' => ['bg' => 'from-blue-500/20 to-cyan-500/20', 'border' => 'border-blue-200', 'icon' => 'fa-info-circle text-blue-500']
                                    ];
                                    $impactColors = [
                                        'high' => 'bg-red-100 text-red-800 border-red-200',
                                        'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200', 
                                        'low' => 'bg-blue-100 text-blue-800 border-blue-200'
                                    ];
                                @endphp
                                <div class="bg-gradient-to-r {{ $typeColors[$insight['type']]['bg'] }} rounded-xl border {{ $typeColors[$insight['type']]['border'] }} p-4 group hover:shadow-md transition-all duration-300">
                                    <div class="flex items-start space-x-3">
                                        <i class="fas {{ $typeColors[$insight['type']]['icon'] }} text-lg mt-1 flex-shrink-0"></i>
                                        <div class="flex-1">
                                            <div class="flex items-start justify-between">
                                                <h4 class="font-semibold text-gray-900 text-sm">{{ $insight['title'] }}</h4>
                                                <span class="text-xs px-2 py-1 rounded-full {{ $impactColors[$insight['impact']] }} font-medium">
                                                    {{ ucfirst($insight['impact']) }} impact
                                                </span>
                                            </div>
                                            <p class="text-gray-600 text-xs mt-2 leading-relaxed">{{ $insight['description'] }}</p>
                                            <div class="flex items-center justify-between mt-3">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                                        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 h-1.5 rounded-full" 
                                                             style="width: {{ $insight['confidence'] }}%"></div>
                                                    </div>
                                                    <span class="text-xs text-gray-500 font-medium">{{ $insight['confidence'] }}% confidence</span>
                                                </div>
                                                <div class="text-xs text-gray-400 capitalize">
                                                    {{ $insight['type'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                
                                @if(count($insights) === 0)
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-chart-line text-gray-400 text-xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm">No insights available</p>
                                    <p class="text-gray-400 text-xs mt-1">Market data is being analyzed</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dominance Leaders & Pattern Performance -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
                    <!-- Dominance Leaders -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Top Dominance Leaders</h2>
                            <div class="text-xs text-gray-500">By regime dominance score</div>
                        </div>
                        <div class="space-y-3">
                            @foreach($dominanceLeaders as $index => $asset)
                            <a href="{{ route('dashboard.symbol-detail', $asset->symbol) }}" 
                               class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200 group hover:border-blue-200 hover:bg-blue-50/50 transition-all duration-300 cursor-pointer">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br 
                                        @if($asset->regime === 'bull') from-green-500 to-emerald-600
                                        @elseif($asset->regime === 'bear') from-red-500 to-rose-600  
                                        @elseif($asset->regime === 'neutral') from-yellow-500 to-amber-600
                                        @elseif($asset->regime === 'volatile') from-purple-500 to-pink-600
                                        @else from-blue-500 to-indigo-600 @endif 
                                        flex items-center justify-center shadow-sm">
                                        <span class="text-white font-bold text-xs">{{ $index + 1 }}</span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">
                                            {{ $asset->symbol }}
                                        </div>
                                        <div class="text-xs text-gray-500 capitalize">{{ $asset->regime }} regime • ${{ number_format($asset->price, 2) }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">{{ $asset->dominance_score }}%</div>
                                    <div class="text-xs text-gray-500">dominance</div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Pattern Performance -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Pattern Performance</h2>
                            <div class="text-xs text-gray-500">Historical accuracy</div>
                        </div>
                        <div class="space-y-4">
                            @foreach($patternPerformance as $pattern)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-200 group hover:border-purple-200 transition-all duration-300">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-sm">
                                        <i class="fas fa-project-diagram text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 text-sm">{{ $pattern['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $pattern['active_count'] }} active patterns</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center space-x-2">
                                        <div class="text-right">
                                            <div class="font-bold text-gray-900">{{ $pattern['accuracy'] }}%</div>
                                            <div class="text-xs text-gray-500">accuracy</div>
                                        </div>
                                        <div class="w-2 h-8 bg-gradient-to-b from-green-400 to-emerald-500 rounded-full opacity-60"></div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Recent Events & Quick Actions -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <!-- Recent Market Events -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Market Events</h2>
                            <div class="text-xs text-gray-500">Last 3 days</div>
                        </div>
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach($recentEvents as $event)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-200 group hover:border-orange-200 transition-all duration-300">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-sm">
                                        <i class="fas fa-bolt text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 text-sm">{{ $event->symbol }}</div>
                                        <div class="text-xs text-gray-500">{{ $event->event_type }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500">{{ $event->triggered_at->format('H:i') }}</div>
                                    <div class="text-xs text-gray-400">{{ $event->triggered_at->format('M j') }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-rocket text-white text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('performance.index') }}" 
                               class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 text-center group hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mx-auto mb-2 shadow-md group-hover:scale-110 transition-all duration-300">
                                    <i class="fas fa-chart-line text-white text-sm"></i>
                                </div>
                                <div class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition-colors duration-300">
                                    Performance Tracker
                                </div>
                            </a>
                            
                            <a href="{{ route('dashboard.regime-detail', 'bull') }}" 
                               class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4 text-center group hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center mx-auto mb-2 shadow-md group-hover:scale-110 transition-all duration-300">
                                    <i class="fas fa-arrow-trend-up text-white text-sm"></i>
                                </div>
                                <div class="font-semibold text-gray-900 text-sm group-hover:text-green-600 transition-colors duration-300">
                                    Bull Regime
                                </div>
                            </a>
                            
                            <a href="{{ route('dashboard.regime-detail', 'bear') }}" 
                               class="bg-gradient-to-br from-red-50 to-rose-50 border border-red-200 rounded-xl p-4 text-center group hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center mx-auto mb-2 shadow-md group-hover:scale-110 transition-all duration-300">
                                    <i class="fas fa-arrow-trend-down text-white text-sm"></i>
                                </div>
                                <div class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors duration-300">
                                    Bear Regime
                                </div>
                            </a>
                            
                            <a href="{{ route('dashboard.regime-detail', 'volatile') }}" 
                               class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-xl p-4 text-center group hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mx-auto mb-2 shadow-md group-hover:scale-110 transition-all duration-300">
                                    <i class="fas fa-wave-square text-white text-sm"></i>
                                </div>
                                <div class="font-semibold text-gray-900 text-sm group-hover:text-purple-600 transition-colors duration-300">
                                    Volatile Regime
                                </div>
                            </a>
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
                <h1 class="text-2xl font-bold text-gray-800">Market Regime Analysis</h1>
                <p class="text-gray-600 mt-2">Please login to view market dashboard</p>
            </div>
        </div>
        @include('layouts.footers.guest.footer')
    @endauth

    @push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .transition-all {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover\:shadow-xl:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .grid > * {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>
    @endpush
</x-layouts.base>