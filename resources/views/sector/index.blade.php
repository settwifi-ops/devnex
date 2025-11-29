@php
    function formatUSD($num) {
        if ($num === null || $num === 0) return '-';
        if ($num >= 1000000000) {
            return '$' . number_format($num / 1000000000, 2) . 'B';
        }
        if ($num >= 1000000) {
            return '$' . number_format($num / 1000000, 2) . 'M';
        }
        if ($num >= 1000) {
            return '$' . number_format($num / 1000, 2) . 'K';
        }
        return '$' . number_format($num, 2);
    }

    function formatPercent($num) {
        if ($num === null) return '-';
        return ($num > 0 ? '+' : '') . number_format($num, 2) . '%';
    }

    function formatDate($dateString) {
        if (!$dateString) return '-';
        try {
            $date = new DateTime($dateString);
            return $date->format('M j, H:i');
        } catch (Exception $e) {
            return '-';
        }
    }
@endphp

<x-layouts.app>

<div class="w-full px-6 py-6 mx-auto">
    <!-- Page Header - Clean dengan Efek -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
        <div class="flex-1">
            <div class="flex items-center mb-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center mr-4 shadow-sm transition-all duration-300 group-hover:shadow-md group-hover:scale-105">
                    <i class="fas fa-layer-group text-white text-sm transition-transform duration-300 group-hover:scale-110"></i>
                </div>
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 transition-colors duration-300 group-hover:text-gray-900">Top Sectors Analysis</h1>
                    <p class="text-gray-500 text-sm mt-1 transition-colors duration-300 group-hover:text-gray-600">24-hour market sector performance & insights</p>
                </div>
            </div>
        </div>

        <!-- Sort Buttons - dengan Efek Interaktif -->
        <div class="flex flex-wrap gap-2">
            <a href="?sort=inflow&page={{ request('page', 1) }}" class="bg-white border border-gray-200 hover:border-blue-300 text-gray-600 font-medium py-2 px-4 rounded-lg flex items-center transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 {{ $sortMode === 'inflow' ? 'bg-blue-50 text-blue-600 border-blue-200 shadow-sm' : '' }} group">
                <i class="fas fa-money-bill-wave mr-2 text-xs transition-transform duration-300 group-hover:scale-110"></i>
                Sort by Inflow
            </a>
            <a href="?sort=percent&page={{ request('page', 1) }}" class="bg-white border border-gray-200 hover:border-green-300 text-gray-600 font-medium py-2 px-4 rounded-lg flex items-center transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 {{ $sortMode === 'percent' ? 'bg-green-50 text-green-600 border-green-200 shadow-sm' : '' }} group">
                <i class="fas fa-chart-line mr-2 text-xs transition-transform duration-300 group-hover:scale-110"></i>
                Sort by %
            </a>
        </div>
    </div>

    <!-- Info Cards dengan Efek Interaktif -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <!-- Total Market Cap -->
        <div class="bg-white/95 backdrop-blur-sm border border-gray-200 rounded-lg p-4 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-1 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center transition-all duration-300 group-hover:shadow-sm group-hover:scale-105">
                    <i class="fas fa-globe text-blue-400 text-sm transition-transform duration-300 group-hover:scale-110"></i>
                </div>
                <div class="text-right">
                    <div class="text-lg font-normal text-gray-900 transition-all duration-300 group-hover:scale-105 group-hover:text-blue-600">
                        {{ formatUSD($totalMarketCap) }}
                    </div>
                    <div class="text-xs text-gray-500 transition-colors duration-300 group-hover:text-blue-500">Total Market Cap</div>
                </div>
            </div>
        </div>

        <!-- Average Change -->
        <div class="bg-white/95 backdrop-blur-sm border border-gray-200 rounded-lg p-4 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-1 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center transition-all duration-300 group-hover:shadow-sm group-hover:scale-105">
                    <i class="fas fa-chart-line text-green-400 text-sm transition-transform duration-300 group-hover:scale-110"></i>
                </div>
                <div class="text-right">
                    @php
                        $avgChange = $averageChange ?? 0;
                        $avgColor = $avgChange >= 0 ? 'text-green-600' : 'text-red-600';
                    @endphp
                    <div class="text-lg font-normal {{ $avgColor }} transition-all duration-300 group-hover:scale-105">
                        {{ formatPercent($avgChange) }}
                    </div>
                    <div class="text-xs text-gray-500 transition-colors duration-300 group-hover:text-green-500">Avg 24h Change</div>
                </div>
            </div>
        </div>

        <!-- Top Performer -->
        <div class="bg-white/95 backdrop-blur-sm border border-gray-200 rounded-lg p-4 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-1 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center transition-all duration-300 group-hover:shadow-sm group-hover:scale-105">
                    <i class="fas fa-trophy text-amber-400 text-sm transition-transform duration-300 group-hover:scale-110"></i>
                </div>
                <div class="text-right">
                    @php
                        $topChange = $topPerformerChange ?? 0;
                        $topColor = $topChange >= 0 ? 'text-amber-600' : 'text-red-600';
                    @endphp
                    <div class="text-lg font-normal {{ $topColor }} transition-all duration-300 group-hover:scale-105">
                        {{ formatPercent($topChange) }}
                    </div>
                    <div class="text-xs text-gray-500 transition-colors duration-300 group-hover:text-amber-500">Top Performer</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content dengan Efek Glass -->
    <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-500 hover:shadow-lg">
        <!-- Table Header -->
        <div class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 px-6 py-4 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <h3 class="text-base font-medium text-gray-700 flex items-center group cursor-pointer">
                    <i class="fas fa-chart-pie mr-2 text-blue-500 transition-transform duration-300 group-hover:scale-110"></i>
                    Sector Performance Overview
                </h3>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-500 bg-white/80 backdrop-blur-sm px-3 py-1.5 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-sm">
                        Showing <span class="text-blue-500 font-medium">{{ $sectors->count() }}</span> of <span class="text-purple-500 font-medium">{{ $totalSectors }}</span> sectors
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-sm">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100/80">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200 group cursor-pointer">
                            <div class="flex items-center transition-colors duration-200 group-hover:text-gray-700">
                                <i class="fas fa-tag text-gray-400 text-xs mr-2 transition-transform duration-300 group-hover:scale-110"></i>
                                <span>Sector</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200 group cursor-pointer">
                            <div class="flex items-center transition-colors duration-200 group-hover:text-gray-700">
                                <i class="fas fa-coins text-gray-400 text-xs mr-2 transition-transform duration-300 group-hover:scale-110"></i>
                                <span>Top Coins</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200 group cursor-pointer">
                            <div class="flex items-center justify-center transition-colors duration-200 group-hover:text-gray-700">
                                <i class="fas fa-chart-bar text-gray-400 text-xs mr-2 transition-transform duration-300 group-hover:scale-110"></i>
                                <span>Market Cap</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200 group cursor-pointer">
                            <div class="flex items-center justify-center transition-colors duration-200 group-hover:text-gray-700">
                                <i class="fas fa-percentage text-gray-400 text-xs mr-2 transition-transform duration-300 group-hover:scale-110"></i>
                                <span>Change (24h)</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200 group cursor-pointer">
                            <div class="flex items-center justify-center transition-colors duration-200 group-hover:text-gray-700">
                                <i class="fas fa-exchange-alt text-gray-400 text-xs mr-2 transition-transform duration-300 group-hover:scale-110"></i>
                                <span>Volume (24h)</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200 group cursor-pointer">
                            <div class="flex items-center justify-center transition-colors duration-200 group-hover:text-gray-700">
                                <i class="fas fa-clock text-gray-400 text-xs mr-2 transition-transform duration-300 group-hover:scale-110"></i>
                                <span>Updated</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if($sectors->count() > 0)
                        @foreach($sectors as $sector)
                            @php
                                $changePercent = $sector->market_cap_change_24h ?? 0;
                                $inflowUsd = $sector->inflow_usd ?? 0;
                                
                                // Parse logos
                                $logos = [];
                                if (is_array($sector->top_3_logos)) {
                                    $logos = $sector->top_3_logos;
                                } elseif (is_string($sector->top_3_logos)) {
                                    $decoded = json_decode($sector->top_3_logos, true);
                                    $logos = is_array($decoded) ? $decoded : [];
                                }
                                
                                $changeColor = $changePercent >= 0 ? 
                                    'bg-green-100 text-green-700 border border-green-200 hover:bg-green-200' : 
                                    'bg-red-100 text-red-700 border border-red-200 hover:bg-red-200';
                                $progressWidth = min(abs($changePercent), 100);
                            @endphp
                            
                            <tr class="transition-all duration-300 hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-purple-50/30 group cursor-pointer" 
                                style="animation-delay: {{ $loop->index * 0.05 }}s">
                                
                                <!-- Sector Name -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center mr-3 transition-all duration-300 group-hover:shadow-sm group-hover:scale-105">
                                            <i class="fas fa-folder text-blue-400 text-xs transition-transform duration-300 group-hover:scale-110"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-normal text-gray-900 transition-colors duration-300 group-hover:text-gray-800">
                                                {{ $sector->name ?? 'Unknown Sector' }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5 transition-colors duration-300 group-hover:text-gray-600">
                                                Inflow: <span class="font-medium">{{ formatUSD($inflowUsd) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Top Coins dengan Efek Hover -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center -space-x-2">
                                        @foreach(array_slice($logos, 0, 3) as $index => $logo)
                                            <div class="relative group/coin">
                                                <div class="w-7 h-7 rounded-full border-2 border-white bg-white shadow-xs overflow-hidden transition-all duration-300 group-hover/coin:shadow-md group-hover/coin:scale-110 group-hover/coin:z-10">
                                                    <img src="{{ $logo }}" 
                                                         onerror="this.src='/assets/img/default.png'" 
                                                         class="w-full h-full object-cover transition-transform duration-300 group-hover/coin:scale-105" 
                                                         alt="coin" />
                                                </div>
                                                <!-- Tooltip Effect -->
                                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover/coin:opacity-100 transition-all duration-300 pointer-events-none whitespace-nowrap z-20">
                                                    Coin {{ $index + 1 }}
                                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if(empty($logos))
                                            <span class="text-xs text-gray-400 transition-colors duration-300 group-hover:text-gray-500">No logos</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Market Cap -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-normal text-gray-900 transition-all duration-300 group-hover:scale-105 group-hover:text-purple-600">
                                        {{ formatUSD($sector->market_cap) }}
                                    </div>
                                </td>

                                <!-- Change Percentage dengan Progress Bar Animasi -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center space-y-1.5">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $changeColor }} transition-all duration-300 group-hover:shadow-sm group-hover:scale-105">
                                            <i class="fas {{ $changePercent >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1 text-xs transition-transform duration-300 group-hover:scale-110"></i>
                                            {{ formatPercent($changePercent) }}
                                        </span>
                                        <!-- Animated Progress Bar -->
                                        <div class="w-12 bg-gray-200 rounded-full h-1 overflow-hidden">
                                            <div class="h-1 rounded-full transition-all duration-1000 {{ $changePercent >= 0 ? 'bg-green-400' : 'bg-red-400' }} group-hover:bg-opacity-80" 
                                                 style="width: 0%"
                                                 data-width="{{ $progressWidth }}">
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Volume -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-normal text-gray-900 transition-all duration-300 group-hover:scale-105 group-hover:text-green-600">
                                        {{ formatUSD($sector->volume_24h) }}
                                    </div>
                                </td>

                                <!-- Updated dengan Live Pulse -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="text-xs text-gray-500 font-normal mb-1 transition-colors duration-300 group-hover:text-gray-700">
                                            {{ formatDate($sector->updated_at_api) }}
                                        </div>
                                        <!-- Animated Live Indicator -->
                                        <div class="flex items-center space-x-1 text-xs text-gray-400 transition-colors duration-300 group-hover:text-gray-500">
                                            <div class="relative">
                                                <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                                                <div class="absolute inset-0 rounded-full bg-green-400 animate-ping opacity-75"></div>
                                            </div>
                                            <span>Live</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="fas fa-inbox text-3xl mb-3 opacity-50 transition-transform duration-300 hover:scale-110"></i>
                                    <p class="text-base font-normal mb-1 transition-colors duration-300 hover:text-gray-500">No sectors data available</p>
                                    <p class="text-sm transition-colors duration-300 hover:text-gray-500">Sector data will appear here once available</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination dengan Efek -->
        @if($sectors->hasPages())
        <div class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 px-6 py-4 border-t border-gray-100">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <!-- Pagination Info -->
                <div class="text-sm text-gray-500 font-normal transition-colors duration-300 hover:text-gray-700">
                    Showing 
                    <span class="text-gray-700 font-medium">{{ $sectors->firstItem() ?? 0 }}</span> 
                    to 
                    <span class="text-gray-700 font-medium">{{ $sectors->lastItem() ?? 0 }}</span> 
                    of 
                    <span class="text-gray-700 font-medium">{{ $sectors->total() }}</span> 
                    results
                </div>

                <!-- Pagination Links -->
                <div class="flex items-center space-x-1">
                    <!-- Previous Page Link -->
                    @if ($sectors->onFirstPage())
                        <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg border border-gray-200 cursor-not-allowed transition-all duration-300">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </span>
                    @else
                        <a href="{{ $sectors->previousPageUrl() }}{{ $sortMode ? '&sort=' . $sortMode : '' }}" 
                           class="px-3 py-1.5 text-sm text-gray-600 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 group">
                            <i class="fas fa-chevron-left mr-1 transition-transform duration-300 group-hover:-translate-x-0.5"></i> Previous
                        </a>
                    @endif

                    <!-- Page Numbers -->
                    @foreach ($sectors->getUrlRange(1, $sectors->lastPage()) as $page => $url)
                        @if ($page == $sectors->currentPage())
                            <span class="px-3 py-1.5 text-sm text-white bg-blue-500 rounded-lg border border-blue-500 shadow-sm transition-all duration-300">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}{{ $sortMode ? '&sort=' . $sortMode : '' }}" 
                               class="px-3 py-1.5 text-sm text-gray-600 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    <!-- Next Page Link -->
                    @if ($sectors->hasMorePages())
                        <a href="{{ $sectors->nextPageUrl() }}{{ $sortMode ? '&sort=' . $sortMode : '' }}" 
                           class="px-3 py-1.5 text-sm text-gray-600 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 group">
                            Next <i class="fas fa-chevron-right ml-1 transition-transform duration-300 group-hover:translate-x-0.5"></i>
                        </a>
                    @else
                        <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg border border-gray-200 cursor-not-allowed transition-all duration-300">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

<style>
    /* Smooth animations and transitions */
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    /* Clean scrollbar dengan efek */
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
        transition: all 0.3s ease;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 6px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Elegant fade-in animation for table rows */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    tbody tr {
        animation: fadeInUp 0.5s ease-out forwards;
        opacity: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate progress bars on page load
        const progressBars = document.querySelectorAll('[data-width]');
        progressBars.forEach(bar => {
            const width = bar.getAttribute('data-width');
            setTimeout(() => {
                bar.style.width = width + '%';
            }, 300);
        });

        // Add ripple effect to table rows
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach((row, index) => {
            // Set animation delay for staggered effect
            row.style.animationDelay = `${index * 0.03}s`;
            
            // Add click effect
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
</script>

</x-layouts.app>