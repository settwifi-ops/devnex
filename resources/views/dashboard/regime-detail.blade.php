<x-layouts.base>
    @auth
        @include('layouts.navbars.auth.sidebar')
        <main class="ease-soft-in-out xl:ml-68.5 relative h-full max-h-screen rounded-xl transition-all duration-200">
            @include('layouts.navbars.auth.nav')
            <div class="w-full px-6 py-6 mx-auto">
                
                <!-- Header -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('dashboard.market') }}" class="bg-white hover:bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-xl flex items-center text-sm transition-all duration-300 shadow-sm border border-gray-200 hover:shadow-md group">
                            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform duration-300"></i>Back to Dashboard
                        </a>
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-xl 
                                @if($regimeType === 'bull') bg-gradient-to-br from-green-500 to-emerald-600
                                @elseif($regimeType === 'bear') bg-gradient-to-br from-red-500 to-rose-600
                                @elseif($regimeType === 'neutral') bg-gradient-to-br from-yellow-500 to-amber-600
                                @elseif($regimeType === 'volatile') bg-gradient-to-br from-purple-500 to-pink-600
                                @else bg-gradient-to-br from-blue-500 to-indigo-600 @endif 
                                flex items-center justify-center mr-4 shadow-lg">
                                <i class="fas fa-chart-line text-white text-base"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 capitalize">{{ $regimeType }} Regime</h1>
                                <p class="text-gray-500 text-sm mt-1">Market regime analysis and asset performance</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 bg-white px-4 py-2 rounded-lg border border-gray-200 font-medium shadow-sm mt-4 lg:mt-0">
                        <span class="text-blue-600 font-semibold">{{ $regimeStats['total_assets'] }}</span> assets detected
                    </div>
                </div>

                <!-- Regime Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Total Assets -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500 group cursor-pointer transform hover:-translate-y-1">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">
                                {{ $regimeStats['total_assets'] }}
                            </div>
                            <div class="text-sm text-gray-600 mt-2">Total Assets</div>
                        </div>
                    </div>

                    <!-- Avg Confidence -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500 group cursor-pointer transform hover:-translate-y-1">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 group-hover:text-green-600 transition-colors duration-300">
                                {{ $regimeStats['avg_confidence'] }}%
                            </div>
                            <div class="text-sm text-gray-600 mt-2">Avg Confidence</div>
                        </div>
                    </div>

                    <!-- Avg Volatility -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500 group cursor-pointer transform hover:-translate-y-1">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 group-hover:text-purple-600 transition-colors duration-300">
                                {{ $regimeStats['avg_volatility'] }}%
                            </div>
                            <div class="text-sm text-gray-600 mt-2">Avg Volatility</div>
                        </div>
                    </div>

                    <!-- Avg Dominance -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500 group cursor-pointer transform hover:-translate-y-1">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 group-hover:text-orange-600 transition-colors duration-300">
                                {{ $regimeStats['avg_dominance'] }}%
                            </div>
                            <div class="text-sm text-gray-600 mt-2">Avg Dominance</div>
                        </div>
                    </div>
                </div>

                <!-- Top Asset -->
                @if($regimeStats['top_asset'])
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8 hover:shadow-xl transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Top Asset in {{ ucfirst($regimeType) }} Regime</h2>
                        <span class="px-3 py-1 bg-gradient-to-br from-yellow-500 to-amber-600 text-white text-sm font-medium rounded-full border border-yellow-200">
                            <i class="fas fa-crown mr-1"></i>Leader
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 group hover:border-blue-200 transition-all duration-300">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-lg">{{ substr($regimeStats['top_asset']->symbol, 0, 3) }}</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">
                                    {{ $regimeStats['top_asset']->symbol }}
                                </div>
                                <div class="text-lg text-gray-600 mt-1">${{ number_format($regimeStats['top_asset']->price, 2) }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500 mb-1">Dominance Score</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $regimeStats['top_asset']->dominance_score }}%</div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Assets Table -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-500">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Assets in {{ ucfirst($regimeType) }} Regime</h2>
                                <p class="text-gray-500 text-xs mt-1">Real-time market regime analysis</p>
                            </div>
                            <div class="text-xs text-gray-600 bg-white px-3 py-1.5 rounded-lg border border-gray-200 font-medium shadow-sm">
                                Showing <span class="text-blue-600 font-semibold">{{ count($regimeAssets) }}</span> assets
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full text-sm">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <div class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-hashtag text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Symbol</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <div class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-dollar-sign text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Price</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <div class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-chart-pie text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Dominance</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <div class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-shield-alt text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Confidence</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <div class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-wave-square text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Volatility</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-100 group cursor-pointer">
                                        <div class="flex items-center hover:text-gray-800 transition-all duration-300">
                                            <i class="fas fa-chart-bar text-gray-400 text-xs mr-2 group-hover:text-blue-500 transition-colors duration-300"></i>
                                            <span>Market Cap</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($regimeAssets as $asset)
                                <tr class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-purple-50/50 transition-all duration-300 group cursor-pointer transform hover:scale-[1.01]">
                                    <!-- Symbol -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('dashboard.symbol-detail', $asset->symbol) }}" 
                                           class="flex items-center group/link">
                                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-3 shadow-sm group-hover/link:shadow-md transition-all duration-300">
                                                <span class="text-white font-bold text-xs">{{ substr($asset->symbol, 0, 2) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900 text-sm group-hover/link:text-blue-600 transition-colors duration-300">
                                                    {{ $asset->symbol }}
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    
                                    <!-- Price -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 group-hover:text-green-600 transition-colors duration-300">
                                            ${{ number_format($asset->price, 2) }}
                                        </div>
                                    </td>
                                    
                                    <!-- Dominance -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-12 bg-gray-200 rounded-full h-1.5 shadow-inner group-hover:w-14 transition-all duration-500">
                                                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 h-1.5 rounded-full transition-all duration-1000 ease-out" 
                                                     style="width: {{ $asset->dominance_score }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-700 min-w-[35px] group-hover:text-blue-600 transition-colors duration-300">
                                                {{ $asset->dominance_score }}%
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Confidence -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $confidence = round($asset->regime_confidence * 100, 1);
                                            $confidenceColor = $confidence >= 80 ? 'from-green-500/20 to-emerald-500/20 text-green-700 border border-green-200' : 
                                                              ($confidence >= 60 ? 'from-blue-500/20 to-cyan-500/20 text-blue-700 border border-blue-200' : 
                                                              'from-yellow-500/20 to-amber-500/20 text-yellow-700 border border-yellow-200');
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gradient-to-r {{ $confidenceColor }} transition-all duration-300 group-hover:shadow-md">
                                            {{ $confidence }}%
                                        </span>
                                    </td>
                                    
                                    <!-- Volatility -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $volatility = round($asset->volatility_24h * 100, 2);
                                            $volatilityColor = $volatility >= 10 ? 'from-red-500/20 to-rose-500/20 text-red-700 border border-red-200' : 
                                                              ($volatility >= 5 ? 'from-orange-500/20 to-amber-500/20 text-orange-700 border border-orange-200' : 
                                                              'from-green-500/20 to-emerald-500/20 text-green-700 border border-green-200');
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gradient-to-r {{ $volatilityColor }} transition-all duration-300 group-hover:shadow-md">
                                            {{ $volatility }}%
                                        </span>
                                    </td>
                                    
                                    <!-- Market Cap -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 group-hover:text-purple-600 transition-colors duration-300">
                                            ${{ number_format($asset->market_cap / 1000000, 2) }}M
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    @if(count($regimeAssets) === 0)
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-tl from-gray-400/20 to-gray-500/20 rounded-full mb-4">
                            <i class="fas fa-chart-line text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-400 text-sm">No assets found in {{ $regimeType }} regime.</p>
                        <p class="text-gray-500 text-xs mt-1">Market conditions may have changed.</p>
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
                <h1 class="text-2xl font-bold text-gray-800">{{ ucfirst($regimeType) }} Regime Analysis</h1>
                <p class="text-gray-600 mt-2">Please login to view regime details</p>
            </div>
        </div>
        @include('layouts.footers.guest.footer')
    @endauth

    @push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom Scrollbar */
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

        /* Smooth transitions for all interactive elements */
        .transition-all {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Hover effects */
        .hover\:shadow-xl:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Animations */
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

        tbody tr {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        tbody tr:nth-child(odd) {
            animation-delay: 0.05s;
        }

        tbody tr:nth-child(even) {
            animation-delay: 0.1s;
        }
    </style>
    @endpush
</x-layouts.base>