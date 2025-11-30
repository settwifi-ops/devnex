<x-layouts.base>
    @auth
        @include('layouts.navbars.auth.sidebar')
        <main class="ease-soft-in-out xl:ml-68.5 relative h-full max-h-screen rounded-xl transition-all duration-200">
            @include('layouts.navbars.auth.nav')
            <div class="w-full px-6 py-6 mx-auto">
                
                <!-- Header -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('performance.index') }}" class="bg-white hover:bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-xl flex items-center text-sm transition-all duration-300 shadow-sm border border-gray-200 hover:shadow-md group">
                            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform duration-300"></i>Back to List
                        </a>
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-4 shadow-lg">
                                <i class="fas fa-chart-line text-white text-base"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $performance->symbol }} Performance</h1>
                                <p class="text-gray-500 text-sm mt-1">Detailed technical metrics and tracking signals</p>
                            </div>
                        </div>
                    </div>
                    <button onclick="generatePNG()" class="bg-gradient-to-br from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-medium py-3 px-6 rounded-xl flex items-center text-sm transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0 group mt-4 lg:mt-0">
                        <i class="fas fa-download mr-2 text-xs"></i>
                        Export as PNG
                    </button>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
                    <!-- Left Column - Key Metrics -->
                    <div class="xl:col-span-2 space-y-6">
                        <!-- Performance Overview -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-semibold text-gray-900">Performance Overview</h2>
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full border border-blue-200">
                                        Rank #{{ $performance->rank }}
                                    </span>
                                    <span class="px-3 py-1 {{ $performance->is_active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-gray-100 text-gray-800 border border-gray-200' }} text-sm font-medium rounded-full">
                                        {{ $performance->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Current Price -->
                                <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100 group hover:border-blue-200 transition-all duration-300 cursor-pointer">
                                    <div class="text-2xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">${{ number_format($performance->current_price, 4) }}</div>
                                    <div class="text-sm text-gray-600 mt-1">Current Price</div>
                                </div>
                                
                                <!-- Performance -->
                                <div class="text-center p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-100 group hover:border-green-200 transition-all duration-300 cursor-pointer">
                                    <div class="text-2xl font-bold {{ $performance->performance_since_first >= 0 ? 'text-green-600 group-hover:text-green-700' : 'text-red-600 group-hover:text-red-700' }} transition-colors duration-300">
                                        {{ $performance->performance_since_first >= 0 ? '+' : '' }}{{ number_format($performance->performance_since_first, 2) }}%
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">Performance</div>
                                </div>
                                
                                <!-- Health Score -->
                                <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border border-purple-100 group hover:border-purple-200 transition-all duration-300 cursor-pointer">
                                    <div class="text-2xl font-bold text-purple-600 group-hover:text-purple-700 transition-colors duration-300">{{ $performance->health_score }}</div>
                                    <div class="text-sm text-gray-600 mt-1">Health Score</div>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Metrics -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                            <div class="flex items-center mb-6">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mr-3 shadow-md">
                                    <i class="fas fa-microchip text-white text-sm"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-gray-900">Technical Metrics</h2>
                            </div>
                            <div class="space-y-4">
                                <!-- Trend Strength -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-700 font-medium">Trend Strength</span>
                                        <span class="text-gray-900 font-semibold">{{ $performance->trend_strength }}/100</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
                                        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 h-3 rounded-full transition-all duration-500" 
                                             style="width: {{ $performance->trend_strength }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Health Score -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-700 font-medium">Health Score</span>
                                        <span class="text-gray-900 font-semibold">{{ $performance->health_score }}/100</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
                                        <div class="bg-gradient-to-r from-green-500 to-emerald-400 h-3 rounded-full transition-all duration-500" 
                                             style="width: {{ $performance->health_score }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Momentum & Risk -->
                                <div class="grid grid-cols-2 gap-4 mt-6">
                                    <div class="text-center p-4 bg-gray-50 rounded-xl border border-gray-200 group hover:border-blue-200 transition-all duration-300 cursor-pointer">
                                        <div class="text-sm text-gray-600 mb-2">Momentum Phase</div>
                                        <div class="text-lg font-semibold {{ $performance->momentum_phase == 'ACCUMULATION' ? 'text-green-600' : 'text-yellow-600' }}">
                                            {{ $performance->momentum_phase }}
                                        </div>
                                    </div>
                                    <div class="text-center p-4 bg-gray-50 rounded-xl border border-gray-200 group hover:border-red-200 transition-all duration-300 cursor-pointer">
                                        <div class="text-sm text-gray-600 mb-2">Risk Level</div>
                                        <div class="text-lg font-semibold 
                                            @if($performance->risk_level == 'LOW') text-green-600
                                            @elseif($performance->risk_level == 'MEDIUM') text-yellow-600
                                            @else text-red-600 @endif">
                                            {{ $performance->risk_level }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Additional Info -->
                    <div class="space-y-6">
                        <!-- Signal Information -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                            <div class="flex items-center mb-4">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center mr-3 shadow-md">
                                    <i class="fas fa-signal text-white text-xs"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-gray-900">Signal Information</h2>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 group hover:bg-gray-50 rounded-lg px-2 transition-all duration-300">
                                    <span class="text-gray-600">Appearance Count</span>
                                    <span class="font-semibold text-gray-900">{{ $performance->appearance_count }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 group hover:bg-gray-50 rounded-lg px-2 transition-all duration-300">
                                    <span class="text-gray-600">Hours Tracked</span>
                                    <span class="font-semibold text-gray-900">{{ number_format($performance->hours_since_first, 1) }}h</span>
                                </div>
                                <div class="flex justify-between items-center py-2 group hover:bg-gray-50 rounded-lg px-2 transition-all duration-300">
                                    <span class="text-gray-600">Days Tracked</span>
                                    <span class="font-semibold text-gray-900">{{ number_format($performance->hours_since_first / 24, 1) }}d</span>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-500">
                            <div class="flex items-center mb-4">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mr-3 shadow-md">
                                    <i class="fas fa-clock text-white text-xs"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-gray-900">Timeline</h2>
                            </div>
                            <div class="space-y-3">
                                <div class="group hover:bg-gray-50 rounded-lg p-2 transition-all duration-300">
                                    <div class="text-sm text-gray-600 mb-1">First Detection</div>
                                    <div class="font-semibold text-gray-900">{{ $performance->first_detection_time->format('M j, Y H:i') }}</div>
                                </div>
                                <div class="group hover:bg-gray-50 rounded-lg p-2 transition-all duration-300">
                                    <div class="text-sm text-gray-600 mb-1">Last Updated</div>
                                    <div class="font-semibold text-gray-900">{{ $performance->last_seen->format('M j, Y H:i') }}</div>
                                </div>
                                <div class="group hover:bg-gray-50 rounded-lg p-2 transition-all duration-300">
                                    <div class="text-sm text-gray-600 mb-1">Data Refreshed</div>
                                    <div class="font-semibold text-gray-900">{{ $performance->updated_at->format('M j, Y H:i') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Confidence Score -->
                        <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-500 transform hover:-translate-y-1">
                            <h2 class="text-lg font-semibold mb-2">Confidence Score</h2>
                            @php
                                $confidenceScore = min(100, max(60, $performance->health_score + $performance->trend_strength / 2));
                            @endphp
                            <div class="text-3xl font-bold mb-2">{{ number_format($confidenceScore) }}%</div>
                            <div class="text-blue-100 text-sm">
                                Based on technical and health metrics
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden PNG Template -->
                <div id="png-template" style="display: none;">
                    <div id="export-container" class="w-[800px] h-[1250px] bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 text-white p-8 font-sans">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-700">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                                </div>
                                <div>
                                    <h1 class="text-5xl font-bold text-white mb-1">
                                        {{ $performance->symbol }}
                                    </h1>
                                    <p class="text-gray-400 text-lg">Performance Analysis Report</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold {{ $performance->performance_since_first >= 0 ? 'text-green-400' : 'text-red-400' }} mb-1">
                                    {{ $performance->performance_since_first >= 0 ? '+' : '' }}{{ number_format($performance->performance_since_first, 2) }}%
                                </div>
                                <div class="text-gray-400 text-sm">Performance Since First</div>
                            </div>
                        </div>

                        <!-- Signal Badge -->
                        <div class="flex justify-center mb-8">
                            <div class="inline-flex items-center bg-slate-800 rounded-full px-8 py-4 border border-slate-700 shadow-lg">
                                <div class="w-4 h-4 rounded-full {{ $performance->performance_since_first >= 0 ? 'bg-green-400 animate-pulse' : 'bg-red-400' }} mr-4"></div>
                                <span class="text-2xl font-bold text-white mr-6">
                                    {{ $performance->performance_since_first >= 0 ? 'BULLISH' : 'BEARISH' }} SIGNAL
                                </span>
                                <div class="px-4 py-2 bg-yellow-500/20 rounded-full border border-yellow-500/30">
                                    <span class="text-yellow-400 font-bold text-xl">{{ number_format($confidenceScore) }}% Confidence</span>
                                </div>
                            </div>
                        </div>

                        <!-- Metrics Grid -->
                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <!-- Technical Metrics -->
                                <div class="bg-slate-800/50 rounded-2xl p-6 border border-slate-700 backdrop-blur-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-microchip text-blue-400 text-lg"></i>
                                        </div>
                                        <h3 class="text-xl font-semibold text-blue-400">Technical Metrics</h3>
                                    </div>
                                    <div class="space-y-5">
                                        <!-- Health Score -->
                                        <div>
                                            <div class="flex justify-between items-center mb-3">
                                                <span class="text-gray-300 text-lg">Health Score</span>
                                                <span class="font-bold text-xl">{{ $performance->health_score }}/100</span>
                                            </div>
                                            <div class="w-full bg-slate-700 rounded-full h-3">
                                                <div class="bg-gradient-to-r from-green-500 to-emerald-400 h-3 rounded-full shadow-lg" 
                                                     style="width: {{ $performance->health_score }}%"></div>
                                            </div>
                                        </div>
                                        
                                        <!-- Trend Strength -->
                                        <div>
                                            <div class="flex justify-between items-center mb-3">
                                                <span class="text-gray-300 text-lg">Trend Strength</span>
                                                <span class="font-bold text-xl">{{ $performance->trend_strength }}/100</span>
                                            </div>
                                            <div class="w-full bg-slate-700 rounded-full h-3">
                                                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 h-3 rounded-full shadow-lg" 
                                                     style="width: {{ $performance->trend_strength }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Momentum & Risk -->
                                <div class="bg-slate-800/50 rounded-2xl p-6 border border-slate-700 backdrop-blur-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-rocket text-purple-400 text-lg"></i>
                                        </div>
                                        <h3 class="text-xl font-semibold text-purple-400">Momentum & Risk</h3>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="text-center p-4 bg-slate-700/50 rounded-xl border border-slate-600">
                                            <div class="text-gray-400 text-sm mb-2">PHASE</div>
                                            <div class="text-sm font-bold {{ $performance->momentum_phase == 'ACCUMULATION' ? 'text-green-400' : 'text-yellow-400' }}">
                                                {{ $performance->momentum_phase }}
                                            </div>
                                        </div>
                                        <div class="text-center p-4 bg-slate-700/50 rounded-xl border border-slate-600">
                                            <div class="text-gray-400 text-sm mb-2">RISK</div>
                                            <div class="text-xl font-bold 
                                                @if($performance->risk_level == 'LOW') text-green-400
                                                @elseif($performance->risk_level == 'MEDIUM') text-yellow-400
                                                @else text-red-400 @endif">
                                                {{ $performance->risk_level }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <!-- Market Data -->
                                <div class="bg-slate-800/50 rounded-2xl p-6 border border-slate-700 backdrop-blur-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-chart-bar text-green-400 text-lg"></i>
                                        </div>
                                        <h3 class="text-xl font-semibold text-green-400">Market Data</h3>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center py-3 border-b border-slate-600">
                                            <span class="text-gray-300 text-lg">Current Price</span>
                                            <span class="font-bold text-xl">${{ number_format($performance->current_price, 4) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3 border-b border-slate-600">
                                            <span class="text-gray-300 text-lg">Signal Count</span>
                                            <span class="font-bold text-xl">{{ $performance->appearance_count }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3">
                                            <span class="text-gray-300 text-lg">Hours Tracked</span>
                                            <span class="font-bold text-xl">{{ number_format($performance->hours_since_first, 1) }}h</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Performance Score -->
                                <div class="bg-slate-800/50 rounded-2xl p-6 border border-slate-700 backdrop-blur-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="w-10 h-10 bg-cyan-500/20 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-star text-cyan-400 text-lg"></i>
                                        </div>
                                        <h3 class="text-xl font-semibold text-cyan-400">Performance Score</h3>
                                    </div>
                                    <div class="text-center">
                                        <div class="relative inline-block mb-4">
                                            <!-- Outer glow -->
                                            <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-blue-500 rounded-full blur-xl opacity-30"></div>
                                            <!-- Main circle -->
                                            <div class="relative w-28 h-28 rounded-full border-4 
                                                @if($performance->health_score >= 70) border-green-400
                                                @elseif($performance->health_score >= 50) border-yellow-400
                                                @else border-red-400 @endif bg-slate-800 flex items-center justify-center">
                                                <div class="text-center">
                                                    <div class="text-3xl font-bold 
                                                        @if($performance->health_score >= 70) text-green-400
                                                        @elseif($performance->health_score >= 50) text-yellow-400
                                                        @else text-red-400 @endif">
                                                        {{ $performance->health_score }}
                                                    </div>
                                                    <div class="text-xs text-gray-400 mt-1">SCORE</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            @if($performance->health_score >= 70)
                                                <span class="bg-green-900/50 text-green-300 text-lg font-semibold px-6 py-3 rounded-full border border-green-600">
                                                    🚀 EXCELLENT HEALTH
                                                </span>
                                            @elseif($performance->health_score >= 50)
                                                <span class="bg-yellow-900/50 text-yellow-300 text-lg font-semibold px-6 py-3 rounded-full border border-yellow-600">
                                                    ⚡ MODERATE HEALTH
                                                </span>
                                            @else
                                                <span class="bg-red-900/50 text-red-300 text-lg font-semibold px-6 py-3 rounded-full border border-red-600">
                                                    ⚠️ CAUTION ADVISED
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Section -->
                        <div class="bg-gradient-to-r from-blue-600/30 to-purple-600/30 rounded-2xl p-8 border border-blue-500/30 backdrop-blur-sm">
                            <h3 class="text-2xl font-bold text-white mb-4 flex items-center">
                                <i class="fas fa-analytics mr-3 text-blue-400"></i>
                                Analysis Summary
                            </h3>
                            <p class="text-gray-200 text-lg leading-relaxed">
                                @if($performance->momentum_phase == 'ACCUMULATION')
                                    📈 <strong class="text-green-400">Accumulation phase detected</strong> with strong technical metrics. 
                                    @if($performance->risk_level == 'LOW')
                                        Low risk profile suggests favorable trading conditions with high probability of upward continuation.
                                    @else
                                        Monitor risk levels while maintaining bullish outlook based on current technical structure.
                                    @endif
                                @else
                                    📉 <strong class="text-yellow-400">Distribution phase active</strong> - exercise caution and monitor key support levels.
                                    @if($performance->risk_level == 'HIGH')
                                        Elevated risk level indicates potential reversal scenario requiring careful position management.
                                    @endif
                                @endif
                                Overall confidence at <strong class="text-yellow-400">{{ number_format($confidenceScore) }}%</strong> based on comprehensive technical and health metrics analysis.
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="flex justify-between items-center mt-8 pt-6 border-t border-slate-700">
                            <div class="flex items-center space-x-3 text-gray-400 text-lg">
                                <i class="fas fa-chart-line text-blue-400"></i>
                                <span>Performance Tracker</span>
                            </div>
                            <div class="text-gray-400 text-lg">
                                Generated: {{ now()->format('M j, Y H:i') }}
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
                <h1 class="text-2xl font-bold text-gray-800">Performance Details</h1>
                <p class="text-gray-600 mt-2">Please login to view performance details</p>
            </div>
        </div>
        @include('layouts.footers.guest.footer')
    @endauth

    <!-- HTML2Canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function generatePNG() {
            const element = document.getElementById('export-container');
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';
            button.disabled = true;

            const pngTemplate = document.getElementById('png-template');
            pngTemplate.style.display = 'block';
            
            html2canvas(element, {
                backgroundColor: '#0F172A',
                scale: 2,
                useCORS: true,
                logging: false,
                width: 800,
                height: 1250
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = '{{ $performance->symbol }}-Performance-{{ now()->format("Y-m-d") }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                
                pngTemplate.style.display = 'none';
                button.innerHTML = originalText;
                button.disabled = false;
            }).catch(error => {
                console.error('Error generating PNG:', error);
                alert('Error generating PNG. Please try again.');
                pngTemplate.style.display = 'none';
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    </script>

    @push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Smooth transitions for all interactive elements */
        .transition-all {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Hover effects for cards */
        .hover\:shadow-xl:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Custom animations */
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
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Gradient text effect */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @endpush
</x-layouts.base>