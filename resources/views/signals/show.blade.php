<x-layouts.base>
    @auth
        @include('layouts.navbars.auth.sidebar')
        <main class="ease-soft-in-out xl:ml-68.5 relative h-full max-h-screen rounded-xl transition-all duration-200">
            @include('layouts.navbars.auth.nav')
            <div class="w-full px-6 py-6 mx-auto">

                <!-- Page Header - Premium -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
                    <div class="flex-1">
                        <div class="flex items-center mb-3">
                            <div class="relative group">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-4 shadow-xl group-hover:shadow-2xl transition-all duration-500 group-hover:scale-105">
                                    <i class="fas fa-chart-line text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-blue-600 group-hover:to-purple-600 group-hover:bg-clip-text transition-all duration-300">{{ $signal->symbol }}</h1>
                                <p class="text-gray-600 text-lg mt-1">{{ $signal->name }}</p>
                            </div>
                        </div>
                        <p class="text-gray-500 text-sm font-medium">Signal Analysis & Market Insights</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <button id="exportBtn" class="bg-gradient-to-br from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl flex items-center justify-center transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0 group">
                            <i class="fas fa-download mr-3 group-hover:scale-110 transition-transform duration-300"></i>
                            Export as PNG
                        </button>

                        <a href="{{ route('signals.index') }}" class="bg-white/80 backdrop-blur-sm hover:bg-gray-50 text-gray-700 border border-gray-200 font-semibold py-3 px-6 rounded-xl flex items-center justify-center transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5">
                            <i class="fas fa-arrow-left mr-3"></i>
                            Back to List
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Score Cards - Premium dengan Floating Effects -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Enhanced Score -->
                            <div class="bg-white/80 backdrop-blur-sm border border-emerald-100 rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-emerald-200 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                                <div class="absolute -inset-0.5 bg-gradient-to-br from-emerald-400/10 to-green-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-semibold text-emerald-600 group-hover:text-emerald-700 transition-colors duration-300">Enhanced Score</span>
                                        <i class="fas fa-star text-emerald-400 group-hover:scale-110 transition-transform duration-300"></i>
                                    </div>
                                    <div class="text-3xl font-bold text-gray-900 group-hover:scale-105 group-hover:text-emerald-600 transition-all duration-300">{{ number_format($signal->enhanced_score ?? 0, 2) }}</div>
                                    <div class="text-xs text-gray-500 group-hover:text-emerald-500 transition-colors duration-300 mt-2">Overall Performance</div>
                                </div>
                            </div>

                            <!-- Confidence -->
                            <div class="bg-white/80 backdrop-blur-sm border border-blue-100 rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-blue-200 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                                <div class="absolute -inset-0.5 bg-gradient-to-br from-blue-400/10 to-cyan-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-semibold text-blue-600 group-hover:text-blue-700 transition-colors duration-300">Confidence</span>
                                        <i class="fas fa-bullseye text-blue-400 group-hover:scale-110 transition-transform duration-300"></i>
                                    </div>
                                    <div class="text-3xl font-bold text-gray-900 group-hover:scale-105 group-hover:text-blue-600 transition-all duration-300">{{ $signal->smart_confidence ?? 0 }}%</div>
                                    <div class="text-xs text-gray-500 group-hover:text-blue-500 transition-colors duration-300 mt-2">Signal Reliability</div>
                                </div>
                            </div>

                            <!-- Health Score -->
                            <div class="bg-white/80 backdrop-blur-sm border border-purple-100 rounded-2xl p-5 shadow-lg hover:shadow-2xl transition-all duration-500 group hover:border-purple-200 cursor-pointer transform hover:-translate-y-1 relative overflow-hidden">
                                <div class="absolute -inset-0.5 bg-gradient-to-br from-purple-400/10 to-pink-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-semibold text-purple-600 group-hover:text-purple-700 transition-colors duration-300">Health Score</span>
                                        <i class="fas fa-heartbeat text-purple-400 group-hover:scale-110 transition-transform duration-300"></i>
                                    </div>
                                    <div class="text-3xl font-bold text-gray-900 group-hover:scale-105 group-hover:text-purple-600 transition-all duration-300">{{ $signal->health_score ?? 0 }}%</div>
                                    <div class="text-xs text-gray-500 group-hover:text-purple-500 transition-colors duration-300 mt-2">Market Condition</div>
                                </div>
                            </div>
                        </div>

                         <!-- Technical Indicators - Premium -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-500">
                            <div class="bg-gradient-to-r from-blue-50/80 to-cyan-50/80 px-6 py-4 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center group cursor-pointer">
                                    <i class="fas fa-chart-bar mr-3 text-blue-500 group-hover:scale-110 transition-transform duration-300"></i>
                                    Technical Indicators
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @php
                                        $technicalItems = [
                                            [
                                                'label' => 'Volume Spike Ratio',
                                                'value' => number_format($signal->volume_spike_ratio ?? 0, 2) . 'x',
                                                'icon' => 'fas fa-wave-square',
                                                'color' => 'text-blue-600'
                                            ],
                                            [
                                                'label' => 'Volume Acceleration',
                                                'value' => number_format($signal->volume_acceleration ?? 0, 2),
                                                'icon' => 'fas fa-tachometer-alt',
                                                'color' => 'text-purple-600'
                                            ],
                                            [
                                                'label' => 'RSI Delta',
                                                'value' => number_format($signal->rsi_delta ?? 0, 2),
                                                'icon' => 'fas fa-chart-area',
                                                'color' => 'text-green-600'
                                            ],
                                            [
                                                'label' => 'Trend Strength',
                                                'value' => number_format($signal->trend_strength ?? 0, 2),
                                                'icon' => 'fas fa-trend-up',
                                                'color' => 'text-orange-600'
                                            ]
                                        ];
                                    @endphp

                                    @foreach($technicalItems as $item)
                                    <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 group cursor-pointer">
                                        <div class="flex items-center justify-between mb-3">
                                            <label class="block text-sm font-medium text-gray-600 group-hover:text-gray-700 transition-colors duration-300">{{ $item['label'] }}</label>
                                            <i class="{{ $item['icon'] }} text-gray-400 group-hover:scale-110 transition-transform duration-300"></i>
                                        </div>
                                        <p class="text-xl font-bold {{ $item['color'] }} group-hover:scale-105 transition-transform duration-300">{{ $item['value'] }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Futures Market Data - Premium -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-500">
                            <div class="bg-gradient-to-r from-indigo-50/80 to-purple-50/80 px-6 py-4 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center group cursor-pointer">
                                    <i class="fas fa-exchange-alt mr-3 text-indigo-500 group-hover:scale-110 transition-transform duration-300"></i>
                                    Futures Market Data
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    @php
                                        $futuresItems = [
                                            [
                                                'label' => 'Open Interest',
                                                'value' => number_format($signal->open_interest ?? 0),
                                                'color' => 'text-purple-600',
                                                'icon' => 'fas fa-chart-bar'
                                            ],
                                            [
                                                'label' => 'OI Change',
                                                'value' => number_format($signal->oi_change ?? 0, 2) . '%',
                                                'color' => ($signal->oi_change ?? 0) > 0 ? 'text-green-600' : 'text-red-600',
                                                'icon' => ($signal->oi_change ?? 0) > 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'
                                            ],
                                            [
                                                'label' => 'Funding Rate',
                                                'value' => number_format($signal->funding_rate ?? 0, 6) . '%',
                                                'color' => ($signal->funding_rate ?? 0) > 0 ? 'text-green-600' : 'text-red-600',
                                                'icon' => 'fas fa-percentage'
                                            ]
                                        ];
                                    @endphp

                                    @foreach($futuresItems as $item)
                                    <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 group cursor-pointer text-center">
                                        <i class="{{ $item['icon'] }} text-gray-400 text-lg mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                                        <p class="text-sm text-gray-500 mb-2 group-hover:text-gray-600 transition-colors duration-300">{{ $item['label'] }}</p>
                                        <p class="text-2xl font-bold {{ $item['color'] }} group-hover:scale-105 transition-transform duration-300">{{ $item['value'] }}</p>
                                    </div>
                                    @endforeach
                                </div>

                                @if($signal->summary)
                                <div class="bg-gradient-to-r from-purple-50/80 to-pink-50/80 rounded-xl p-5 border-l-4 border-purple-400 shadow-sm hover:shadow-md transition-all duration-300">
                                    <h4 class="text-md font-semibold text-purple-700 mb-3 flex items-center">
                                        <i class="fas fa-comment-alt mr-2 group-hover:scale-110 transition-transform duration-300"></i>Market Summary
                                    </h4>
                                    <div class="bg-white/80 backdrop-blur-sm border border-purple-100 p-4 rounded-lg text-gray-700 leading-relaxed text-sm">
                                        {!! nl2br(e($signal->summary)) !!}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar Info - Premium -->
                    <div class="space-y-6">
                        <!-- Signal Metadata -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-500">
                            <div class="bg-gradient-to-r from-gray-50/80 to-white px-6 py-4 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center group cursor-pointer">
                                    <i class="fas fa-info-circle mr-3 text-gray-500 group-hover:scale-110 transition-transform duration-300"></i>
                                    Signal Details
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    @php
                                        $riskColors = [
                                            'VERY_LOW' => 'bg-green-100 text-green-800 border border-green-200 hover:bg-green-200',
                                            'LOW' => 'bg-green-100 text-green-800 border border-green-200 hover:bg-green-200',
                                            'MEDIUM' => 'bg-yellow-100 text-yellow-800 border border-yellow-200 hover:bg-yellow-200',
                                            'HIGH' => 'bg-red-100 text-red-800 border border-red-200 hover:bg-red-200'
                                        ];
                                        $momentumColors = [
                                            'STRONG_BULL' => 'bg-green-100 text-green-800 border border-green-200 hover:bg-green-200',
                                            'BULLISH' => 'bg-green-100 text-green-800 border border-green-200 hover:bg-green-200',
                                            'NEUTRAL' => 'bg-gray-100 text-gray-800 border border-gray-200 hover:bg-gray-200',
                                            'BEARISH' => 'bg-red-100 text-red-800 border border-red-200 hover:bg-red-200'
                                        ];
                                    @endphp

                                    @foreach([
                                        ['label' => 'Risk Level', 'value' => $signal->risk_level, 'color' => $riskColors[$signal->risk_level] ?? 'bg-gray-100 text-gray-800'],
                                        ['label' => 'Momentum Regime', 'value' => $signal->momentum_regime, 'color' => $momentumColors[$signal->momentum_regime] ?? 'bg-gray-100 text-gray-800'],
                                        ['label' => 'Momentum Phase', 'value' => $signal->momentum_phase],
                                        ['label' => 'Appearance Count', 'value' => $signal->appearance_count],
                                        ['label' => 'Hours Since First', 'value' => $signal->hours_since_first . 'h'],
                                        ['label' => 'Latest Update', 'value' => $signal->latest_update],
                                        ['label' => 'First Detection', 'value' => $signal->first_detection_time->format('M j, Y H:i')],
                                        ['label' => 'Last Updated', 'value' => $signal->timestamp->format('M j, Y H:i')]
                                    ] as $item)
                                    <div class="flex justify-between items-center p-4 bg-gray-50/80 rounded-xl border border-gray-200 hover:bg-gray-100/80 transition-all duration-300 group cursor-pointer">
                                        <span class="text-sm font-medium text-gray-600 group-hover:text-gray-700 transition-colors duration-300">{{ $item['label'] }}</span>
                                        @if(isset($item['color']))
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $item['color'] }} transition-colors duration-300">{{ $item['value'] }}</span>
                                        @else
                                        <span class="text-sm font-semibold text-gray-900 group-hover:text-gray-700 transition-colors duration-300">{{ $item['value'] }}</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions - Premium -->
                        <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <i class="fas fa-rocket mr-2 group-hover:scale-110 transition-transform duration-300"></i>Quick Actions
                            </h3>
                            <div class="space-y-3">
                                <button id="exportBtnSidebar" class="w-full bg-white/20 hover:bg-white/30 text-white font-semibold py-3 px-4 rounded-xl flex items-center justify-center transition-all duration-300 backdrop-blur-sm border border-white/20 hover:border-white/30 group">
                                    <i class="fas fa-download mr-3 group-hover:scale-110 transition-transform duration-300"></i>Export Signal Card
                                </button>
                                <a href="#" class="w-full bg-white/20 hover:bg-white/30 text-white font-semibold py-3 px-4 rounded-xl flex items-center justify-center transition-all duration-300 backdrop-blur-sm border border-white/20 hover:border-white/30 group">
                                    <i class="fas fa-share-alt mr-3 group-hover:scale-110 transition-transform duration-300"></i>Share Analysis
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Hidden Template for PNG Export (Premium Dark Theme) -->
                <div id="export-template" style="display:none; width:1200px; height:800px; position:fixed; left:-9999px; top:-9999px;">
                    <div style="width:1200px; height:800px; padding:60px; box-sizing:border-box; background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%); color:white; font-family: 'Inter', sans-serif;">

                        <!-- Header dengan efek premium -->
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:50px; padding-bottom:30px; border-bottom:1px solid rgba(255,255,255,0.1);">
                            <div style="display:flex; align-items:center; gap:25px;">
                                <div style="width:80px; height:80px; border-radius:20px; background:linear-gradient(135deg, #667eea, #764ba2); display:flex; align-items:center; justify-content:center; box-shadow:0 20px 60px rgba(102,126,234,0.4); font-size:32px;">
                                    📈
                                </div>
                                <div>
                                    <div style="font-size:42px; font-weight:800; letter-spacing:1px; background:linear-gradient(135deg, #fff, #dcd0ff); -webkit-background-clip:text; -webkit-text-fill-color:transparent; margin-bottom:5px;">{{ $signal->symbol }}</div>
                                    <div style="font-size:20px; color:rgba(255,255,255,0.7);">{{ $signal->name }}</div>
                                </div>
                            </div>

                            <div style="text-align:right;">
                                <div style="font-size:28px; font-weight:700; color:white; margin-bottom:5px;">${{ number_format($signal->current_price ?? 0, 6) }}</div>
                                <div style="font-size:16px; color:{{ ($signal->price_change_24h ?? 0) > 0 ? '#00ff88' : '#ff6b6b' }}; font-weight:600;">
                                    {{ number_format($signal->price_change_24h ?? 0, 2) }}% (24h)
                                </div>
                            </div>
                        </div>

                        <!-- Main Metrics dengan efek glassmorphism -->
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:25px; margin-bottom:50px;">
                            @foreach([
                                ['label' => 'Enhanced Score', 'value' => number_format($signal->enhanced_score ?? 0, 2), 'gradient' => 'linear-gradient(135deg, #00ff88, #00ccff)'],
                                ['label' => 'Confidence', 'value' => ($signal->smart_confidence ?? 0) . '%', 'gradient' => 'linear-gradient(135deg, #ff6b6b, #ffd93d)'],
                                ['label' => 'Health Score', 'value' => ($signal->health_score ?? 0) . '%', 'gradient' => 'linear-gradient(135deg, #6a11cb, #2575fc)']
                            ] as $metric)
                            <div style="background:linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03)); border-radius:20px; padding:30px; text-align:center; border:1px solid rgba(255,255,255,0.1); backdrop-filter:blur(10px);">
                                <div style="font-size:18px; color:#dcd0ff; margin-bottom:15px; font-weight:600;">{{ $metric['label'] }}</div>
                                <div style="font-size:56px; font-weight:900; background:{{ $metric['gradient'] }}; -webkit-background-clip:text; -webkit-text-fill-color:transparent; margin-bottom:10px; line-height:1;">
                                    {{ $metric['value'] }}
                                </div>
                                <div style="font-size:14px; color:rgba(255,255,255,0.6);">{{ $signal->momentum_regime }}</div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Technical Data Grid -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px;">
                            <!-- Market Metrics -->
                            <div>
                                <div style="font-size:24px; font-weight:700; color:white; margin-bottom:25px; background:linear-gradient(135deg, #fff, #dcd0ff); -webkit-background-clip:text; -webkit-text-fill-color:transparent; padding-bottom:10px; border-bottom:2px solid rgba(255,255,255,0.1);">
                                    📊 Market Metrics
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                                    @foreach([
                                        ['label' => 'Open Interest', 'value' => number_format($signal->open_interest ?? 0), 'icon' => '🔗'],
                                        ['label' => 'Funding Rate', 'value' => number_format($signal->funding_rate ?? 0, 6) . '%', 'icon' => '💰', 'color' => ($signal->funding_rate ?? 0) > 0 ? '#00ff88' : '#ff6b6b'],
                                        ['label' => 'Volume Spike', 'value' => number_format($signal->volume_spike_ratio ?? 0, 2) . 'x', 'icon' => '📈'],
                                        ['label' => 'RSI Delta', 'value' => number_format($signal->rsi_delta ?? 0, 2), 'icon' => '🎯']
                                    ] as $item)
                                    <div style="background:rgba(255,255,255,0.05); padding:20px; border-radius:15px; border:1px solid rgba(255,255,255,0.1); backdrop-filter:blur(5px);">
                                        <div style="font-size:14px; color:#cbb7ff; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                                            <span>{{ $item['icon'] }}</span>
                                            <span>{{ $item['label'] }}</span>
                                        </div>
                                        <div style="font-weight:800; font-size:22px; color:{{ $item['color'] ?? 'white' }};">{{ $item['value'] }}</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Price Performance -->
                            <div>
                                <div style="font-size:24px; font-weight:700; color:white; margin-bottom:25px; background:linear-gradient(135deg, #fff, #dcd0ff); -webkit-background-clip:text; -webkit-text-fill-color:transparent; padding-bottom:10px; border-bottom:2px solid rgba(255,255,255,0.1);">
                                    💹 Price Performance
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                                    @foreach([
                                        ['label' => '1H Change', 'value' => number_format($signal->price_change_1h ?? 0, 2) . '%', 'icon' => '⏱️'],
                                        ['label' => '24H Change', 'value' => number_format($signal->price_change_24h ?? 0, 2) . '%', 'icon' => '📅'],
                                        ['label' => 'Since First', 'value' => number_format($signal->performance_since_first ?? 0, 2) . '%', 'icon' => '🚀']
                                    ] as $item)
                                    <div style="background:rgba(255,255,255,0.05); padding:20px; border-radius:15px; border:1px solid rgba(255,255,255,0.1); backdrop-filter:blur(5px); text-align:center;">
                                        <div style="font-size:14px; color:#cbb7ff; margin-bottom:8px; display:flex; align-items:center; justify-content:center; gap:8px;">
                                            <span>{{ $item['icon'] }}</span>
                                            <span>{{ $item['label'] }}</span>
                                        </div>
                                        <div style="font-weight:800; font-size:22px; color:{{ (float)str_replace('%', '', $item['value']) > 0 ? '#00ff88' : '#ff6b6b' }};">{{ $item['value'] }}</div>
                                    </div>
                                    @endforeach
                                    <div style="grid-column:1/-1; background:linear-gradient(135deg, rgba(102,126,234,0.2), rgba(118,75,162,0.2)); padding:20px; border-radius:15px; border:1px solid rgba(255,255,255,0.1); text-align:center;">
                                        <div style="font-size:14px; color:#cbb7ff; margin-bottom:8px;">Risk Level</div>
                                        <div style="font-weight:800; font-size:22px; color:white; text-transform:uppercase;">{{ $signal->risk_level }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div style="margin-top:40px; display:flex; justify-content:space-between; align-items:center; padding-top:25px; border-top:1px solid rgba(255,255,255,0.1);">
                            <div>
                                <div style="font-size:20px; color:#d9ccff; font-weight:700; margin-bottom:5px;">Devnex Research</div>
                                <div style="font-size:14px; color:rgba(255,255,255,0.6);">AI-Powered Market Analysis</div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:14px; color:rgba(255,255,255,0.6); margin-bottom:5px;">Generated</div>
                                <div style="font-weight:700; color:white; font-size:16px;">{{ now()->format('M j, Y H:i') }}</div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Scripts -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
                <script>
                    // Export functionality dengan loading state
                    async function exportAsPNG() {
                        const exportBtn = document.getElementById('exportBtn');
                        const originalText = exportBtn.innerHTML;
                        
                        // Loading state
                        exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-3"></i>Generating...';
                        exportBtn.disabled = true;

                        const template = document.getElementById('export-template');
                        template.style.display = 'block';

                        try {
                            await new Promise(resolve => setTimeout(resolve, 500));
                            
                            const canvas = await html2canvas(template, {
                                scale: 2,
                                useCORS: true,
                                backgroundColor: null,
                                logging: false,
                                imageTimeout: 15000,
                                width: 1200,
                                height: 800
                            });

                            const image = canvas.toDataURL('image/png', 1.0);
                            const link = document.createElement('a');
                            link.href = image;
                            link.download = `Devnex-Signal-{{ $signal->symbol }}-${new Date().getTime()}.png`;
                            document.body.appendChild(link);
                            link.click();
                            link.remove();

                        } catch (err) {
                            console.error('Export error', err);
                            alert('Error generating image: ' + err.message);
                        } finally {
                            template.style.display = 'none';
                            exportBtn.innerHTML = originalText;
                            exportBtn.disabled = false;
                        }
                    }

                    // Attach event listeners
                    document.getElementById('exportBtn').addEventListener('click', exportAsPNG);
                    document.getElementById('exportBtnSidebar').addEventListener('click', exportAsPNG);

                    // Add hover effects to all interactive elements
                    document.addEventListener('DOMContentLoaded', function() {
                        const interactiveElements = document.querySelectorAll('.cursor-pointer');
                        interactiveElements.forEach(el => {
                            el.addEventListener('mouseenter', function() {
                                this.style.transform = 'translateY(-2px)';
                            });
                            el.addEventListener('mouseleave', function() {
                                this.style.transform = 'translateY(0)';
                            });
                        });
                    });
                </script>

                @include('layouts.footers.auth.footer')
            </div>
        </main>
    @else
        @include('layouts.navbars.guest.nav')
        <div class="w-full px-6 py-6 mx-auto">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800">Signal Details</h1>
                <p class="text-gray-600 mt-2">Please login to view signal details</p>
            </div>
        </div>
        @include('layouts.footers.guest.footer')
    @endauth
</x-layouts.base>