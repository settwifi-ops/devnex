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
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-amber-600 flex items-center justify-center mr-4 shadow-lg">
                                <i class="fas fa-coins text-white text-base"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $symbolData->symbol }}</h1>
                                <p class="text-gray-500 text-sm mt-1">Detailed market analysis and technical indicators</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 bg-white px-4 py-2 rounded-lg border border-gray-200 font-medium shadow-sm mt-4 lg:mt-0">
                        <i class="fas fa-calendar mr-2 text-blue-500"></i>
                        {{ $symbolData->date->format('M j, Y') }}
                    </div>
                </div>

                <!-- Symbol Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Current Price -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500 group cursor-pointer transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition-colors duration-300">Current Price</div>
                                <div class="text-2xl font-bold text-gray-900 mt-1 group-hover:text-green-600 transition-colors duration-300">
                                    ${{ number_format($symbolData->price, 2) }}
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-dollar-sign text-white text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Market Regime -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500 group cursor-pointer transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition-colors duration-300">Market Regime</div>
                                <div class="flex items-center mt-1">
                                    <span class="text-lg font-bold text-gray-900 capitalize mr-2 group-hover:text-purple-600 transition-colors duration-300">
                                        {{ $symbolData->regime }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border
                                        @if($symbolData->regime === 'bull') bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 border-green-200
                                        @elseif($symbolData->regime === 'bear') bg-gradient-to-r from-red-500/20 to-rose-500/20 text-red-700 border-red-200
                                        @elseif($symbolData->regime === 'neutral') bg-gradient-to-r from-yellow-500/20 to-amber-500/20 text-yellow-700 border-yellow-200
                                        @elseif($symbolData->regime === 'volatile') bg-gradient-to-r from-purple-500/20 to-pink-500/20 text-purple-700 border-purple-200
                                        @else bg-gradient-to-r from-blue-500/20 to-cyan-500/20 text-blue-700 border-blue-200 @endif">
                                        {{ round($symbolData->regime_confidence * 100, 1) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl 
                                @if($symbolData->regime === 'bull') bg-gradient-to-br from-green-500 to-emerald-600
                                @elseif($symbolData->regime === 'bear') bg-gradient-to-br from-red-500 to-rose-600
                                @elseif($symbolData->regime === 'neutral') bg-gradient-to-br from-yellow-500 to-amber-600
                                @elseif($symbolData->regime === 'volatile') bg-gradient-to-br from-purple-500 to-pink-600
                                @else bg-gradient-to-br from-blue-500 to-indigo-600 @endif 
                                flex items-center justify-center shadow-md group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-chart-line text-white text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Dominance Score -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500 group cursor-pointer transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition-colors duration-300">Dominance Score</div>
                                <div class="text-2xl font-bold text-gray-900 mt-1 group-hover:text-orange-600 transition-colors duration-300">
                                    {{ $symbolData->dominance_score }}%
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-chart-pie text-white text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <!-- 24h Volatility -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500 group cursor-pointer transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition-colors duration-300">24h Volatility</div>
                                <div class="text-2xl font-bold text-gray-900 mt-1 group-hover:text-purple-600 transition-colors duration-300">
                                    {{ round($symbolData->volatility_24h * 100, 2) }}%
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-all duration-300">
                                <i class="fas fa-wave-square text-white text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Technical Indicators -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-microchip text-white text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Technical Indicators</h2>
                        </div>
                        <div class="space-y-4">
                            <!-- RSI -->
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-200 group hover:border-blue-200 transition-all duration-300">
                                <span class="text-gray-600 font-medium">RSI (14)</span>
                                <span class="font-semibold text-sm px-3 py-1 rounded-full border
                                    @if($symbolData->rsi_14 > 70) bg-gradient-to-r from-red-500/20 to-rose-500/20 text-red-700 border-red-200
                                    @elseif($symbolData->rsi_14 < 30) bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 border-green-200
                                    @else bg-gradient-to-r from-gray-500/20 to-gray-600/20 text-gray-700 border-gray-200 @endif">
                                    {{ $symbolData->rsi_14 ?? 'N/A' }}
                                </span>
                            </div>
                            
                            <!-- MACD -->
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-200 group hover:border-blue-200 transition-all duration-300">
                                <span class="text-gray-600 font-medium">MACD</span>
                                <span class="font-semibold text-sm px-3 py-1 rounded-full border
                                    @if($symbolData->macd > 0) bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 border-green-200
                                    @elseif($symbolData->macd < 0) bg-gradient-to-r from-red-500/20 to-rose-500/20 text-red-700 border-red-200
                                    @else bg-gradient-to-r from-gray-500/20 to-gray-600/20 text-gray-700 border-gray-200 @endif">
                                    {{ $symbolData->macd ? number_format($symbolData->macd, 4) : 'N/A' }}
                                </span>
                            </div>
                            
                            <!-- Market Cap -->
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-200 group hover:border-blue-200 transition-all duration-300">
                                <span class="text-gray-600 font-medium">Market Cap</span>
                                <span class="font-semibold text-gray-900">
                                    ${{ number_format($symbolData->market_cap / 1000000, 2) }}M
                                </span>
                            </div>
                            
                            <!-- Volume -->
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-200 group hover:border-blue-200 transition-all duration-300">
                                <span class="text-gray-600 font-medium">Volume</span>
                                <span class="font-semibold text-gray-900">
                                    ${{ number_format($symbolData->volume, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Regime History -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-history text-white text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Regime History (30 Days)</h2>
                        </div>
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach(array_slice($regimeHistory, -10) as $history)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-200 group hover:border-purple-200 transition-all duration-300">
                                <span class="text-sm text-gray-600 font-medium">{{ $history['date'] }}</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border
                                    @if($history['regime'] === 'bull') bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 border-green-200
                                    @elseif($history['regime'] === 'bear') bg-gradient-to-r from-red-500/20 to-rose-500/20 text-red-700 border-red-200
                                    @elseif($history['regime'] === 'neutral') bg-gradient-to-r from-yellow-500/20 to-amber-500/20 text-yellow-700 border-yellow-200
                                    @elseif($history['regime'] === 'volatile') bg-gradient-to-r from-purple-500/20 to-pink-500/20 text-purple-700 border-purple-200
                                    @else bg-gradient-to-r from-blue-500/20 to-cyan-500/20 text-blue-700 border-blue-200 @endif">
                                    {{ ucfirst($history['regime']) }} 
                                    <span class="ml-1 text-xs opacity-75">({{ $history['confidence'] }}%)</span>
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Price and Dominance Chart -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center mr-3 shadow-md">
                                <i class="fas fa-chart-line text-white text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Price & Dominance Trend</h2>
                        </div>
                        <div class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-lg border border-gray-200">
                            Last 30 days
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="priceChart"></canvas>
                    </div>
                </div>

                @include('layouts.footers.auth.footer')
            </div>
        </main>
    @else
        @include('layouts.navbars.guest.nav')
        <div class="w-full px-6 py-6 mx-auto">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800">{{ $symbolData->symbol }} Analysis</h1>
                <p class="text-gray-600 mt-2">Please login to view symbol details</p>
            </div>
        </div>
        @include('layouts.footers.guest.footer')
    @endauth

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Price and Dominance Chart
        document.addEventListener('DOMContentLoaded', function() {
            const priceCtx = document.getElementById('priceChart').getContext('2d');
            const priceChart = new Chart(priceCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(collect($regimeHistory)->pluck('date')) !!},
                    datasets: [
                        {
                            label: 'Price (USD)',
                            data: {!! json_encode(collect($regimeHistory)->pluck('price')) !!},
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            yAxisID: 'y',
                            tension: 0.4,
                            pointBackgroundColor: '#3B82F6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Dominance (%)',
                            data: {!! json_encode(collect($regimeHistory)->pluck('dominance')) !!},
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            yAxisID: 'y1',
                            tension: 0.4,
                            pointBackgroundColor: '#10B981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    family: "'Inter', sans-serif"
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1f2937',
                            bodyColor: '#374151',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        if (context.dataset.yAxisID === 'y') {
                                            label += '$' + context.parsed.y.toFixed(2);
                                        } else {
                                            label += context.parsed.y.toFixed(1) + '%';
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: "'Inter', sans-serif"
                                },
                                maxRotation: 0
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    family: "'Inter', sans-serif"
                                },
                                callback: function(value) {
                                    return '$' + value.toFixed(2);
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                font: {
                                    family: "'Inter', sans-serif"
                                },
                                callback: function(value) {
                                    return value.toFixed(1) + '%';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

    @push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom Scrollbar */
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

        .grid > * {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .grid > *:nth-child(1) { animation-delay: 0.1s; }
        .grid > *:nth-child(2) { animation-delay: 0.2s; }
        .grid > *:nth-child(3) { animation-delay: 0.3s; }
        .grid > *:nth-child(4) { animation-delay: 0.4s; }
    </style>
    @endpush
</x-layouts.base>