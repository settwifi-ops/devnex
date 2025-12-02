<x-layouts.base>
    @auth
        @include('layouts.navbars.auth.sidebar')
        <main class="ease-soft-in-out xl:ml-68.5 relative h-full max-h-screen rounded-xl transition-all duration-200">
            @include('layouts.navbars.auth.nav')
            <div class="w-full px-6 py-4 mx-auto">
                
                <!-- Page Header -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-book mr-3 text-purple-600"></i>AI Engine Documentation
                        </h2>
                        <p class="text-gray-600 mt-2">Complete guide to understanding AI signals, performance tracking, and early detection engine</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="scrollToSection('ai-signals')" class="bg-gradient-to-tl from-purple-600 to-pink-500 hover:from-purple-500 hover:to-pink-400 text-white font-semibold py-2 px-4 rounded-lg flex items-center text-sm transition-all duration-300">
                            <i class="fas fa-chart-line mr-2"></i>AI Signals
                        </button>
                        <button onclick="scrollToSection('performance-tracking')" class="bg-gradient-to-tl from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-semibold py-2 px-4 rounded-lg flex items-center text-sm transition-all duration-300">
                            <i class="fas fa-tachometer-alt mr-2"></i>Performance
                        </button>
                        <button onclick="scrollToSection('early-detection')" class="bg-gradient-to-tl from-green-600 to-emerald-500 hover:from-green-500 hover:to-emerald-400 text-white font-semibold py-2 px-4 rounded-lg flex items-center text-sm transition-all duration-300">
                            <i class="fas fa-bolt mr-2"></i>Early Detection
                        </button>
                    </div>
                </div>

                <!-- Documentation Content -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Sidebar Navigation -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-lg sticky top-24">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="font-semibold text-gray-800 text-base flex items-center">
                                    <i class="fas fa-list-ul mr-2 text-purple-600"></i>
                                    Documentation
                                </h3>
                            </div>
                            <nav class="p-4 space-y-1">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">AI Signals Guide</div>
                                <a href="#quick-summary" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group">
                                    <i class="fas fa-bolt mr-3 text-gray-400 group-hover:text-purple-600 text-xs"></i>
                                    Quick Summary
                                </a>
                                <a href="#example-signal" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group">
                                    <i class="fas fa-code mr-3 text-gray-400 group-hover:text-purple-600 text-xs"></i>
                                    Example Signal
                                </a>
                                <a href="#field-explanation" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group">
                                    <i class="fas fa-table mr-3 text-gray-400 group-hover:text-purple-600 text-xs"></i>
                                    Field Explanation
                                </a>
                                <a href="#priority-system" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group">
                                    <i class="fas fa-sort-amount-up mr-3 text-gray-400 group-hover:text-purple-600 text-xs"></i>
                                    Priority System
                                </a>
                                <a href="#decision-rules" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group">
                                    <i class="fas fa-chess-board mr-3 text-gray-400 group-hover:text-purple-600 text-xs"></i>
                                    Decision Rules
                                </a>
                                <a href="#code-reference" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group">
                                    <i class="fas fa-file-code mr-3 text-gray-400 group-hover:text-purple-600 text-xs"></i>
                                    Code Reference
                                </a>
                                <a href="#troubleshooting" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group">
                                    <i class="fas fa-tools mr-3 text-gray-400 group-hover:text-purple-600 text-xs"></i>
                                    Troubleshooting
                                </a>
                                <a href="#glossary" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group">
                                    <i class="fas fa-book mr-3 text-gray-400 group-hover:text-purple-600 text-xs"></i>
                                    Glossary
                                </a>

                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 mt-4">Performance Tracking</div>
                                <a href="#performance-overview" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                                    <i class="fas fa-chart-bar mr-3 text-gray-400 group-hover:text-blue-600 text-xs"></i>
                                    Overview
                                </a>
                                <a href="#performance-example" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                                    <i class="fas fa-code mr-3 text-gray-400 group-hover:text-blue-600 text-xs"></i>
                                    Example
                                </a>
                                <a href="#performance-fields" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                                    <i class="fas fa-table mr-3 text-gray-400 group-hover:text-blue-600 text-xs"></i>
                                    Fields
                                </a>
                                <a href="#momentum-phases" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                                    <i class="fas fa-wave-square mr-3 text-gray-400 group-hover:text-blue-600 text-xs"></i>
                                    Momentum Phases
                                </a>
                                <a href="#performance-alerts" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                                    <i class="fas fa-bell mr-3 text-gray-400 group-hover:text-blue-600 text-xs"></i>
                                    Alerts
                                </a>
                                <a href="#trading-guide" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 group">
                                    <i class="fas fa-graduation-cap mr-3 text-gray-400 group-hover:text-blue-600 text-xs"></i>
                                    Trading Guide
                                </a>

                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 mt-4">Early Detection</div>
                                <a href="#early-detection" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-green-50 hover:text-green-700 transition-all duration-200 group">
                                    <i class="fas fa-binoculars mr-3 text-gray-400 group-hover:text-green-600 text-xs"></i>
                                    Early Detection Engine
                                </a>
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-3 space-y-8">
                        <!-- AI Signals Section -->
                        <section id="ai-signals">
                            <div class="bg-gradient-to-r from-purple-600 to-pink-500 p-6 rounded-t-xl">
                                <h2 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-chart-line mr-3"></i>
                                    AI Signals — Full Guide
                                </h2>
                                <p class="text-purple-100 mt-2">This document explains every field a signal may contain, how the AI engine computes metrics, and how to interpret them.</p>
                            </div>
                            
                            <div class="bg-white rounded-b-xl shadow-lg">
                                <!-- Quick Summary -->
                                <div id="quick-summary" class="p-6 border-b border-gray-200">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                                        1. Quick Summary
                                    </h3>
                                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4">
                                        <p class="text-gray-700">Each signal object represents a coin detected by the screener with raw metrics (price, volume, RSI), derived analytics (health_score, trend_strength, momentum_phase), and a <code class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-sm font-mono">smart_confidence</code> score which the engine uses to rank signals. Use <code class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-mono">health_score</code> and <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-mono">trend_strength</code> for quality checks; follow <code class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm font-mono">risk_level</code> and <code class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm font-mono">momentum_phase</code> for tactical actions.</p>
                                    </div>
                                </div>

                                <!-- Example Signal -->
                                <div id="example-signal" class="p-6 border-b border-gray-200">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-code mr-2 text-blue-500"></i>
                                        2. Example Signal (JSON)
                                    </h3>
                                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                        <pre class="text-sm text-green-400 font-mono">
{
  <span class="text-cyan-400">"symbol"</span>: <span class="text-yellow-400">"ABC"</span>,
  <span class="text-cyan-400">"name"</span>: <span class="text-yellow-400">"ABC Token"</span>,
  <span class="text-cyan-400">"current_price"</span>: <span class="text-yellow-400">0.1234</span>,
  <span class="text-cyan-400">"current_score"</span>: <span class="text-yellow-400">48</span>,
  <span class="text-cyan-400">"enhanced_score"</span>: <span class="text-yellow-400">62.1</span>,
  <span class="text-cyan-400">"price_change_1h"</span>: <span class="text-yellow-400">3.2</span>,
  <span class="text-cyan-400">"price_change_4h"</span>: <span class="text-yellow-400">8.5</span>,
  <span class="text-cyan-400">"price_change_24h"</span>: <span class="text-yellow-400">12.4</span>,
  <span class="text-cyan-400">"market_cap"</span>: <span class="text-yellow-400">25000000</span>,
  <span class="text-cyan-400">"total_volume"</span>: <span class="text-yellow-400">1500000</span>,
  <span class="text-cyan-400">"volume_spike_ratio"</span>: <span class="text-yellow-400">2.3</span>,
  <span class="text-cyan-400">"volume_acceleration"</span>: <span class="text-yellow-400">0.18</span>,
  <span class="text-cyan-400">"volume_consistency"</span>: <span class="text-yellow-400">0.82</span>,
  <span class="text-cyan-400">"volume_surge"</span>: <span class="text-yellow-400">1.6</span>,
  <span class="text-cyan-400">"rsi_fast"</span>: <span class="text-yellow-400">78.2</span>,
  <span class="text-cyan-400">"rsi_slow"</span>: <span class="text-yellow-400">56.7</span>,
  <span class="text-cyan-400">"rsi_delta"</span>: <span class="text-yellow-400">21.5</span>,
  <span class="text-cyan-400">"momentum_regime"</span>: <span class="text-yellow-400">"STRONG_BULL"</span>,
  <span class="text-cyan-400">"momentum_phase"</span>: <span class="text-yellow-400">"ACCELERATION"</span>,
  <span class="text-cyan-400">"smart_confidence"</span>: <span class="text-yellow-400">78</span>,
  <span class="text-cyan-400">"health_score"</span>: <span class="text-yellow-400">84</span>,
  <span class="text-cyan-400">"trend_strength"</span>: <span class="text-yellow-400">72</span>,
  <span class="text-cyan-400">"risk_level"</span>: <span class="text-yellow-400">"LOW"</span>,
  <span class="text-cyan-400">"appearance_count"</span>: <span class="text-yellow-400">4</span>,
  <span class="text-cyan-400">"performance_since_first"</span>: <span class="text-yellow-400">18.3</span>,
  <span class="text-cyan-400">"score_improvement"</span>: <span class="text-yellow-400">14.1</span>,
  <span class="text-cyan-400">"smart_alerts"</span>: [<span class="text-yellow-400">"💎 Excellent health score"</span>, <span class="text-yellow-400">"⚡ Strong momentum with confirmation"</span>],
  <span class="text-cyan-400">"order_blocks_count"</span>: <span class="text-yellow-400">2</span>,
  <span class="text-cyan-400">"order_block_alignment"</span>: <span class="text-yellow-400">"NEAR_SUPPORT"</span>,
  <span class="text-cyan-400">"order_block_score"</span>: <span class="text-yellow-400">0.72</span>,
  <span class="text-cyan-400">"multi_tf_confirmation"</span>: <span class="text-yellow-400">"BULL"</span>,
  <span class="text-cyan-400">"binance_ratio"</span>: <span class="text-yellow-400">0.4</span>,
  <span class="text-cyan-400">"liquidity_score"</span>: <span class="text-yellow-400">0.7</span>,
  <span class="text-cyan-400">"market_context_score"</span>: <span class="text-yellow-400">0.62</span>
}</pre>
                                    </div>
                                    <p class="text-gray-600 mt-3 text-sm"><i class="fas fa-info-circle mr-1 text-blue-500"></i> This is a condensed example of the structure produced by <code class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-mono">get_display_data()</code>.</p>
                                </div>

                                <!-- Field Explanation -->
                                <div id="field-explanation" class="p-6 border-b border-gray-200">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-table mr-2 text-green-500"></i>
                                        3. Field-by-Field Explanation & Interpretation
                                    </h3>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left text-gray-700">
                                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3">Field</th>
                                                    <th class="px-4 py-3">Meaning</th>
                                                    <th class="px-4 py-3">How to read / thresholds</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <!-- Table rows for all fields -->
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">symbol</td>
                                                    <td class="px-4 py-3">Exchange symbol (e.g. BTC, ETH, ABC)</td>
                                                    <td class="px-4 py-3">Primary identifier</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">current_price</td>
                                                    <td class="px-4 py-3">Latest market price (quote USD/USDT)</td>
                                                    <td class="px-4 py-3">Used to compute performance and proximity to order blocks</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">current_score</td>
                                                    <td class="px-4 py-3">Base/initial screening score (0–100)</td>
                                                    <td class="px-4 py-3">Raw screening result before confidence & momentum adjustments</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">enhanced_score</td>
                                                    <td class="px-4 py-3">Final score after confidence, momentum & market adjustments</td>
                                                    <td class="px-4 py-3">Higher → more attractive. Engine uses this to sort and filter new signals</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">price_change_1h/4h/24h</td>
                                                    <td class="px-4 py-3">Percent price movement over timeframes</td>
                                                    <td class="px-4 py-3">Use to judge short-term momentum. Large 1h increase + volume spike indicates a pump</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">volume_spike_ratio</td>
                                                    <td class="px-4 py-3">Recent volume / historical volume</td>
                                                    <td class="px-4 py-3">&gt;2.0 = big spike (risky but strong). Engine thresholds: surge >2.0 is notable</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">volume_acceleration</td>
                                                    <td class="px-4 py-3">Weighted acceleration of volume (multi-window)</td>
                                                    <td class="px-4 py-3">Positive & large → confirming bullish interest. Used in trend strength calc</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">volume_consistency / volume_surge</td>
                                                    <td class="px-4 py-3">Consistency metric for volume and surge normalization</td>
                                                    <td class="px-4 py-3">High consistency (>0.75) + surge → healthier trend; low consistency → spikes are erratic</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">rsi_fast / rsi_slow / rsi_delta</td>
                                                    <td class="px-4 py-3">RSI (fast & slow) and their difference (momentum delta)</td>
                                                    <td class="px-4 py-3">rsi_fast &gt; rsi_slow (large rsi_delta positive) signals accelerating momentum</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">momentum_regime</td>
                                                    <td class="px-4 py-3">Global momentum label (e.g. STRONG_BULL, BULL, NEUTRAL, BEAR)</td>
                                                    <td class="px-4 py-3">Used as categorical signal for confidence and risk</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">momentum_phase</td>
                                                    <td class="px-4 py-3">Lifecycle phase: ACCUMULATION, ACCELERATION, PARABOLIC, DISTRIBUTION, CAPITULATION, CONSOLIDATION</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 mr-1">ACCUMULATION = early</span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 mr-1">ACCELERATION = entry zone</span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 text-red-800">PARABOLIC = high risk</span>
                                                    </td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">health_score</td>
                                                    <td class="px-4 py-3">Composite quality metric (0–100) combining performance, volume, momentum, consistency</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 mr-1">&gt;75 = healthy</span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 text-red-800">&lt;45 = risky</span>
                                                    </td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">trend_strength</td>
                                                    <td class="px-4 py-3">Numeric trend power (0–100) used to rank the signal</td>
                                                    <td class="px-4 py-3">Higher = stronger validated trend. Derived from performance, volume strength, momentum, RSI delta</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">smart_confidence</td>
                                                    <td class="px-4 py-3">Engine confidence combining market context, volume_profile, momentum, orderblocks</td>
                                                    <td class="px-4 py-3">Used with enhanced_score and health_score to set priority and Telegram updates</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">risk_level</td>
                                                    <td class="px-4 py-3">CONVERTED RISK: VERY_LOW, LOW, MEDIUM, HIGH</td>
                                                    <td class="px-4 py-3">If <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 text-red-800">HIGH</span>, treat as high-risk—prefer taking profits or staying out</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">appearance_count</td>
                                                    <td class="px-4 py-3">How many times coin has been reported by the screener</td>
                                                    <td class="px-4 py-3">Multiple appearances increase reliability</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">performance_since_first</td>
                                                    <td class="px-4 py-3">Percent change since first detection</td>
                                                    <td class="px-4 py-3">Use to estimate realized pump/dump since detection</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">score_improvement</td>
                                                    <td class="px-4 py-3">Difference between current enhanced_score and first_score</td>
                                                    <td class="px-4 py-3">Large positive → accelerating interest; triggers "SCORE SURGE" messages</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">smart_alerts</td>
                                                    <td class="px-4 py-3">List of short human-readable alerts generated by tracking</td>
                                                    <td class="px-4 py-3">Examples: "💎 Excellent health score", "🎚️ Volume surge detected"</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">order_blocks_count / order_block_alignment / order_block_score</td>
                                                    <td class="px-4 py-3">Supply/demand blocks count and alignment relative to price</td>
                                                    <td class="px-4 py-3">NEAR_SUPPORT + high order_block_score = price near strong demand—lower-risk entry</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">multi_tf_confirmation</td>
                                                    <td class="px-4 py-3">Multi-timeframe confirmation label</td>
                                                    <td class="px-4 py-3">BULL / NEUTRAL / BEAR across the most important TFs. Use as confirmation layer</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">binance_ratio / liquidity_score</td>
                                                    <td class="px-4 py-3">Liquidity proxies—exchange presence & relative volume</td>
                                                    <td class="px-4 py-3">Low binance_ratio or liquidity_score suggests low tradability — higher slippage & risk</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">market_context_score</td>
                                                    <td class="px-4 py-3">Global market context (BTC trend + Fear&Greed adjustments)</td>
                                                    <td class="px-4 py-3">Lower scores indicate unfavorable market regimes; used to downweight confidence & score</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-purple-700 bg-purple-50 border-l-4 border-purple-500">sector_table</td>
                                                    <td class="px-4 py-3">Table to validate whether this coin is from a sector that is currently in the top 10 money inflows</td>
                                                    <td class="px-4 py-3">If the top 10 badge appears, the coin will have more potential to increase</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Priority System -->
                                <div id="priority-system" class="p-6 border-b border-gray-200">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-sort-amount-up mr-2 text-blue-500"></i>
                                        4. How the Engine Decides Priority / Urgency
                                    </h3>
                                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-gray-700">The engine computes a combined priority using <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-mono">enhanced_score</code>, <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-mono">smart_confidence</code>, <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-mono">health_score</code>, <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-mono">trend_strength</code>, absolute performance and appearance count. The aggregator returns a category:</p>
                                        <div class="flex flex-wrap gap-2 mt-3">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-red-500 to-pink-500 text-white">
                                                <i class="fas fa-fire mr-1"></i>🔥 HIGHEST
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-orange-500 to-yellow-500 text-white">
                                                <i class="fas fa-rocket mr-1"></i>🚀 HIGH
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-yellow-500 to-amber-500 text-white">
                                                <i class="fas fa-chart-line mr-1"></i>📈 MEDIUM-HIGH
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-500 to-gray-600 text-white">
                                                <i class="fas fa-minus mr-1"></i>LOW
                                            </span>
                                        </div>
                                        <p class="text-gray-700 mt-3">Use <span class="font-semibold text-green-600">HIGHEST/HIGH</span> to trigger immediate attention and possible allocation; <span class="font-semibold text-yellow-600">MEDIUM</span> to watch; <span class="font-semibold text-red-600">LOW</span> to ignore.</p>
                                    </div>
                                </div>

                                <!-- Decision Rules -->
                                <div id="decision-rules" class="p-6 border-b border-gray-200">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-chess-board mr-2 text-green-500"></i>
                                        5. Practical Reading — Decision Rules (Examples)
                                    </h3>
                                    <div class="space-y-4">
                                        <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                                            <h4 class="font-semibold text-green-800 mb-2 flex items-center">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Buy Candidate (Conservative)
                                            </h4>
                                            <ul class="text-sm text-gray-700 space-y-1">
                                                <li class="flex items-center">
                                                    <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                                    health_score &gt; 75 AND trend_strength &gt; 60
                                                </li>
                                                <li class="flex items-center">
                                                    <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                                    smart_confidence &gt; 65 AND multi_tf_confirmation == "BULL"
                                                </li>
                                                <li class="flex items-center">
                                                    <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                                    order_block_alignment == "NEAR_SUPPORT" preferred
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                                            <h4 class="font-semibold text-yellow-800 mb-2 flex items-center">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Speculative Entry (Higher Risk)
                                            </h4>
                                            <ul class="text-sm text-gray-700 space-y-1">
                                                <li class="flex items-center">
                                                    <i class="fas fa-check text-yellow-500 mr-2 text-xs"></i>
                                                    enhanced_score &gt; 65 AND volume_spike_ratio &gt; 2.0 AND health_score &gt; 60
                                                </li>
                                                <li class="flex items-center">
                                                    <i class="fas fa-info-circle text-yellow-500 mr-2 text-xs"></i>
                                                    If momentum_phase == "PARABOLIC" → consider taking partial profits or setting tight SL
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                                            <h4 class="font-semibold text-red-800 mb-2 flex items-center">
                                                <i class="fas fa-times-circle mr-2"></i>
                                                Avoid / Exit
                                            </h4>
                                            <ul class="text-sm text-gray-700 space-y-1">
                                                <li class="flex items-center">
                                                    <i class="fas fa-times text-red-500 mr-2 text-xs"></i>
                                                    risk_level == "HIGH" OR health_score &lt; 45
                                                </li>
                                                <li class="flex items-center">
                                                    <i class="fas fa-times text-red-500 mr-2 text-xs"></i>
                                                    performance_since_first &gt; 40% with momentum_phase == "DISTRIBUTION" (possible top)
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 mt-3 text-sm"><i class="fas fa-lightbulb mr-1 text-yellow-500"></i> These are examples — adapt thresholds to your strategy and position sizing rules.</p>
                                </div>

                                <!-- Code Reference -->
                                <div id="code-reference" class="p-6 border-b border-gray-200">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-file-code mr-2 text-purple-500"></i>
                                        6. Where Values Come From in the Code (Reference)
                                    </h3>
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                                <i class="fas fa-code text-blue-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Display payload / DB fields</h4>
                                                <p class="text-sm text-gray-600">Function <code class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-mono">get_display_data(signal)</code> composes all display fields</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                                <i class="fas fa-heart text-green-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Health score</h4>
                                                <p class="text-sm text-gray-600">Computed in <code class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-mono">_calculate_enhanced_health_score</code> — weighted factors: performance, score improvement, volume trend, momentum trend, price trend, appearance_count, consistency</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                                <i class="fas fa-chart-line text-purple-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Trend strength</h4>
                                                <p class="text-sm text-gray-600">Computed in <code class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-mono">_calculate_enhanced_trend_strength</code>, uses performance magnitude, volume strength, momentum strength, RSI delta, appearance strength</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                                <i class="fas fa-brain text-yellow-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Smart confidence</h4>
                                                <p class="text-sm text-gray-600">Computed in <code class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-mono">calculate_enhanced_smart_confidence</code> — aggregates market_context, volume_profile, momentum regime, order blocks, etc.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Troubleshooting -->
                                <div id="troubleshooting" class="p-6 border-b border-gray-200">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-tools mr-2 text-orange-500"></i>
                                        7. Quick Troubleshooting
                                    </h3>
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Missing fields</h4>
                                                <p class="text-sm text-gray-600">If a signal lacks a field, check the data pipeline step that extracts klines/volumes — default fallbacks exist in the code (e.g. default RSI=50, volume_spike=1)</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                                <i class="fas fa-info-circle text-yellow-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Low confidence even with pump</h4>
                                                <p class="text-sm text-gray-600">Market context (BTC trend & Fear&Greed) can reduce smart_confidence. See MarketContextAnalyzer</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                                <i class="fas fa-cogs text-blue-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Order block detection noisy</h4>
                                                <p class="text-sm text-gray-600">OrderBlockLiteAnalyzer requires min lookback; if klines < threshold it returns empty. Increase lookback data</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                                <i class="fas fa-eye text-purple-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">Signals not showing in UI</h4>
                                                <p class="text-sm text-gray-600">The screener saves UI signals into <code class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-mono">self.last_ui_signals</code> and DB via the database manager — check DB insert logs for errors</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Glossary -->
                                <div id="glossary" class="p-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-book mr-2 text-indigo-500"></i>
                                        8. Glossary (Short)
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                            <h4 class="font-semibold text-gray-800 mb-2">Health score</h4>
                                            <p class="text-sm text-gray-600">Composite gauge of signal robustness (0–100)</p>
                                        </div>
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                            <h4 class="font-semibold text-gray-800 mb-2">Trend strength</h4>
                                            <p class="text-sm text-gray-600">How strong the detected trend is (0–100)</p>
                                        </div>
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                            <h4 class="font-semibold text-gray-800 mb-2">Smart confidence</h4>
                                            <p class="text-sm text-gray-600">Model confidence combining market, volume and orderblock signals</p>
                                        </div>
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                            <h4 class="font-semibold text-gray-800 mb-2">Order block</h4>
                                            <p class="text-sm text-gray-600">Historical area of price rejection/absorption (demand/supply)</p>
                                        </div>
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                            <h4 class="font-semibold text-gray-800 mb-2">Momentum phase</h4>
                                            <p class="text-sm text-gray-600">Lifecycle stage of the move (guides trade urgency)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Performance Tracking Section -->
                        <section id="performance-tracking" class="bg-white rounded-xl shadow-lg overflow-hidden mt-8">
                            <div class="bg-gradient-to-r from-blue-600 to-cyan-500 p-6">
                                <h2 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-tachometer-alt mr-3"></i>
                                    Performance Tracking — Full Guide
                                </h2>
                                <p class="text-blue-100 mt-2">How the bot tracks historical performance and momentum phases</p>
                            </div>
                            
                            <div class="p-6 space-y-6">
                                <!-- Overview -->
                                <div id="performance-overview" class="border-b border-gray-200 pb-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-chart-bar mr-2 text-blue-500"></i>
                                        1. Overview of the Performance Tracking System
                                    </h3>
                                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-gray-700 mb-3">The performance engine records every coin from the moment it first appears in the screener. Over time, it updates:</p>
                                        <ul class="text-gray-700 space-y-2">
                                            <li class="flex items-center">
                                                <i class="fas fa-arrow-up text-green-500 mr-2 text-xs"></i>
                                                Price evolution and percent change
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-chart-line text-blue-500 mr-2 text-xs"></i>
                                                Score progression
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-wave-square text-purple-500 mr-2 text-xs"></i>
                                                Momentum phase transitions
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-redo text-yellow-500 mr-2 text-xs"></i>
                                                Appearance frequency
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-volume-up text-orange-500 mr-2 text-xs"></i>
                                                Volume and trend behaviors
                                            </li>
                                        </ul>
                                        <p class="text-gray-700 mt-3">This forms the <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-mono">tracking_performance</code> object inside <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-mono">get_display_data()</code>, and is used by dashboard data, and priority ranking.</p>
                                    </div>
                                </div>

                                <!-- Performance Example -->
                                <div id="performance-example" class="border-b border-gray-200 pb-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-code mr-2 text-green-500"></i>
                                        2. Example – Tracking Performance JSON
                                    </h3>
                                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                        <pre class="text-sm text-green-400 font-mono">
{
  <span class="text-cyan-400">"first_price"</span>: <span class="text-yellow-400">0.091</span>,
  <span class="text-cyan-400">"current_price"</span>: <span class="text-yellow-400">0.127</span>,
  <span class="text-cyan-400">"performance_since_first"</span>: <span class="text-yellow-400">39.56</span>,
  <span class="text-cyan-400">"score_improvement"</span>: <span class="text-yellow-400">18.2</span>,
  <span class="text-cyan-400">"appearance_count"</span>: <span class="text-yellow-400">5</span>,
  <span class="text-cyan-400">"normalized_performance"</span>: <span class="text-yellow-400">0.68</span>,
  <span class="text-cyan-400">"momentum_phase"</span>: <span class="text-yellow-400">"ACCELERATION"</span>,
  <span class="text-cyan-400">"alerts"</span>: [
    <span class="text-yellow-400">"🔥 Strong normalized growth"</span>,
    <span class="text-yellow-400">"📈 Momentum accelerating"</span>,
    <span class="text-yellow-400">"⭐ Multiple reappearances confirm trend"</span>
  ]
}</pre>
                                    </div>
                                </div>

                                <!-- Performance Fields -->
                                <div id="performance-fields" class="border-b border-gray-200 pb-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-table mr-2 text-purple-500"></i>
                                        3. Field-by-Field Explanation & Interpretation
                                    </h3>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left text-gray-700">
                                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3">Field</th>
                                                    <th class="px-4 py-3">Description</th>
                                                    <th class="px-4 py-3">How to interpret</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-blue-700 bg-blue-50 border-l-4 border-blue-500">first_price</td>
                                                    <td class="px-4 py-3">The price of the coin when the engine first detected it</td>
                                                    <td class="px-4 py-3">Reference point for all future performance calculations</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-blue-700 bg-blue-50 border-l-4 border-blue-500">current_price</td>
                                                    <td class="px-4 py-3">Latest market price from Binance klines</td>
                                                    <td class="px-4 py-3">Used to compute performance_since_first</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-blue-700 bg-blue-50 border-l-4 border-blue-500">performance_since_first</td>
                                                    <td class="px-4 py-3">Percent gain/loss since detection</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span>
                                                        <span class="text-green-600 font-semibold mr-2">+20% to +80%</span> = strong uptrend<br>
                                                        <span class="inline-block w-3 h-3 bg-yellow-500 rounded-full mr-1"></span>
                                                        <span class="text-yellow-600 font-semibold mr-2">+80% to +200%</span> = likely topping<br>
                                                        <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-1"></span>
                                                        <span class="text-red-600 font-semibold">negative %</span> = downtrend / fading momentum
                                                    </td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-blue-700 bg-blue-50 border-l-4 border-blue-500">score_improvement</td>
                                                    <td class="px-4 py-3">The difference between current enhanced_score and the first score when detected</td>
                                                    <td class="px-4 py-3">
                                                        High improvement = strengthening interest<br>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 mr-1">+10+ = healthy</span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">+20+ = speculative acceleration</span>
                                                    </td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-blue-700 bg-blue-50 border-l-4 border-blue-500">appearance_count</td>
                                                    <td class="px-4 py-3">How many times the screener flagged this coin</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 mr-1">5+ appearances</span> = reliable trend confirmation<br>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">1–2</span> = early discovery phase
                                                    </td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-blue-700 bg-blue-50 border-l-4 border-blue-500">normalized_performance</td>
                                                    <td class="px-4 py-3">Performance compressed into 0–1 scale using log smoothing</td>
                                                    <td class="px-4 py-3">Helps classify momentum phases evenly, prevents extreme pumps from exploding scores</td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-blue-700 bg-blue-50 border-l-4 border-blue-500">momentum_phase</td>
                                                    <td class="px-4 py-3">Lifecycle phase based on performance, score delta, and appearance frequency</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 mr-1 mb-1">ACCUMULATION — early stage</span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 mr-1 mb-1">ACCELERATION — ideal entry window</span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 mr-1 mb-1">PARABOLIC — high pump, risky</span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 text-red-800 mr-1 mb-1">DISTRIBUTION — topping signal</span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-800">CAPITULATION — sharp reversal</span>
                                                    </td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-blue-700 bg-blue-50 border-l-4 border-blue-500">Since</td>
                                                    <td class="px-4 py-3">Age since detection</td>
                                                    <td class="px-4 py-3">You can monitor the performance from early detection, for example it increased by
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 ml-1">+8%</span> after 6 hours
                                                    </td>
                                                </tr>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-mono text-blue-700 bg-blue-50 border-l-4 border-blue-500">alerts</td>
                                                    <td class="px-4 py-3">Human-readable performance-based signals</td>
                                                    <td class="px-4 py-3">Used in Telegram performance update messages</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Momentum Phases -->
                                <div id="momentum-phases" class="border-b border-gray-200 pb-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-wave-square mr-2 text-purple-500"></i>
                                        4. How Momentum Phases Are Determined
                                    </h3>
                                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4 mb-4">
                                        <p class="text-gray-700 font-semibold mb-2">The engine uses a multi-factor approach:</p>
                                        <ul class="text-gray-700 space-y-1">
                                            <li class="flex items-center">
                                                <i class="fas fa-chart-line text-purple-500 mr-2 text-xs"></i>
                                                <strong>normalized_performance</strong> (0–1 scale)
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-trending-up text-blue-500 mr-2 text-xs"></i>
                                                <strong>score_improvement</strong> trend
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-redo text-green-500 mr-2 text-xs"></i>
                                                <strong>appearance_count</strong> growth
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-volume-up text-orange-500 mr-2 text-xs"></i>
                                                <strong>volume & RSI behavior</strong>
                                            </li>
                                        </ul>
                                    </div>

                                    <h4 class="font-semibold text-gray-800 mb-3">Phase Rules</h4>
                                    <div class="space-y-3">
                                        <div class="border border-green-200 rounded-lg p-3 bg-green-50">
                                            <h5 class="font-semibold text-green-800 mb-1">ACCUMULATION</h5>
                                            <p class="text-sm text-gray-700">normalized_performance &lt; 0.20 and appearance_count ≤ 2</p>
                                        </div>
                                        <div class="border border-blue-200 rounded-lg p-3 bg-blue-50">
                                            <h5 class="font-semibold text-blue-800 mb-1">ACCELERATION</h5>
                                            <p class="text-sm text-gray-700">0.20–0.55 normalized_performance AND positive score_improvement</p>
                                        </div>
                                        <div class="border border-yellow-200 rounded-lg p-3 bg-yellow-50">
                                            <h5 class="font-semibold text-yellow-800 mb-1">PARABOLIC</h5>
                                            <p class="text-sm text-gray-700">normalized_performance &gt; 0.55 OR performance_since_first &gt; 60%</p>
                                            <p class="text-xs text-yellow-700 mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>High risk of reversal</p>
                                        </div>
                                        <div class="border border-red-200 rounded-lg p-3 bg-red-50">
                                            <h5 class="font-semibold text-red-800 mb-1">DISTRIBUTION</h5>
                                            <p class="text-sm text-gray-700">performance_since_first high but score_improvement declining</p>
                                        </div>
                                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                            <h5 class="font-semibold text-gray-800 mb-1">CAPITULATION</h5>
                                            <p class="text-sm text-gray-700">Strong negative performance or severe volume drop</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Performance Alerts -->
                                <div id="performance-alerts" class="border-b border-gray-200 pb-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-bell mr-2 text-yellow-500"></i>
                                        5. Alerts Generated by the Tracking Engine
                                    </h3>
                                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-lg p-4">
                                        <p class="text-gray-700 mb-3">The engine creates alerts based on performance context to help users quickly understand what is happening.</p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div class="flex items-center p-2 bg-white rounded border">
                                                <i class="fas fa-fire text-red-500 mr-3 text-lg"></i>
                                                <div>
                                                    <div class="font-semibold text-gray-800">Strong normalized growth</div>
                                                    <div class="text-xs text-gray-600">performance rising smoothly</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center p-2 bg-white rounded border">
                                                <i class="fas fa-bolt text-yellow-500 mr-3 text-lg"></i>
                                                <div>
                                                    <div class="font-semibold text-gray-800">Acceleration detected</div>
                                                    <div class="text-xs text-gray-600">score up + performance up together</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center p-2 bg-white rounded border">
                                                <i class="fas fa-chart-line text-blue-500 mr-3 text-lg"></i>
                                                <div>
                                                    <div class="font-semibold text-gray-800">Trend continuation likely</div>
                                                    <div class="text-xs text-gray-600">multiple reappearances</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center p-2 bg-white rounded border">
                                                <i class="fas fa-exclamation-triangle text-orange-500 mr-3 text-lg"></i>
                                                <div>
                                                    <div class="font-semibold text-gray-800">Overextended / parabolic</div>
                                                    <div class="text-xs text-gray-600">potential top</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center p-2 bg-white rounded border">
                                                <i class="fas fa-skull text-red-500 mr-3 text-lg"></i>
                                                <div>
                                                    <div class="font-semibold text-gray-800">Capitulation risk</div>
                                                    <div class="text-xs text-gray-600">collapse likely</div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-gray-700 mt-3 text-sm">Alerts combine performance, appearance_count, and volatility conditions to create accurate warnings.</p>
                                    </div>
                                </div>

                                <!-- Trading Guide -->
                                <div id="trading-guide" class="pb-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-graduation-cap mr-2 text-green-500"></i>
                                        6. Decision Guide — How to Use Tracking in Trades
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                                            <h4 class="font-semibold text-green-800 mb-3 flex items-center">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Best Opportunities (High Probability)
                                            </h4>
                                            <ul class="text-sm text-gray-700 space-y-2">
                                                <li class="flex items-center">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800 mr-2">ACCUMULATION</span>
                                                    + low volatility
                                                </li>
                                                <li class="flex items-center">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 mr-2">ACCELERATION</span>
                                                    + rising score
                                                </li>
                                                <li class="flex items-center">
                                                    <i class="fas fa-redo text-green-500 mr-2 text-xs"></i>
                                                    appearance_count ≥ 3
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                                            <h4 class="font-semibold text-yellow-800 mb-3 flex items-center">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Short-Term Scalping Zones
                                            </h4>
                                            <ul class="text-sm text-gray-700 space-y-2">
                                                <li class="flex items-center">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 mr-2">PARABOLIC</span>
                                                </li>
                                                <li class="flex items-center">
                                                    <i class="fas fa-chart-line text-yellow-500 mr-2 text-xs"></i>
                                                    performance_since_first &gt; 40%
                                                </li>
                                                <li class="flex items-center">
                                                    <i class="fas fa-paper-plane text-yellow-500 mr-2 text-xs"></i>
                                                    Telegram may send "Trend Still Climbing" updates
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                                            <h4 class="font-semibold text-red-800 mb-3 flex items-center">
                                                <i class="fas fa-times-circle mr-2"></i>
                                                Risky or Exit Zones
                                            </h4>
                                            <ul class="text-sm text-gray-700 space-y-2">
                                                <li class="flex items-center">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 text-red-800 mr-2">DISTRIBUTION</span>
                                                </li>
                                                <li class="flex items-center">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-800 mr-2">CAPITULATION</span>
                                                </li>
                                                <li class="flex items-center">
                                                    <i class="fas fa-chart-line-down text-red-500 mr-2 text-xs"></i>
                                                    score_improvement turning negative
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Storage -->
                                <div class="border-t border-gray-200 pt-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-database mr-2 text-blue-500"></i>
                                        7. How the Engine Stores and Updates Performance Data
                                    </h3>
                                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-gray-700 mb-3">Each time the screener detects the same coin:</p>
                                        <ol class="text-gray-700 space-y-2 ml-4">
                                            <li class="flex items-start">
                                                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold mr-2 mt-0.5">1</span>
                                                If first appearance → create tracking entry
                                            </li>
                                            <li class="flex items-start">
                                                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold mr-2 mt-0.5">2</span>
                                                Else → update fields:
                                                <ul class="ml-6 mt-1 space-y-1 text-sm">
                                                    <li class="flex items-center">
                                                        <i class="fas fa-dollar-sign text-green-500 mr-2 text-xs"></i>
                                                        current_price
                                                    </li>
                                                    <li class="flex items-center">
                                                        <i class="fas fa-percentage text-blue-500 mr-2 text-xs"></i>
                                                        performance_since_first
                                                    </li>
                                                    <li class="flex items-center">
                                                        <i class="fas fa-trending-up text-purple-500 mr-2 text-xs"></i>
                                                        score_improvement
                                                    </li>
                                                    <li class="flex items-center">
                                                        <i class="fas fa-redo text-yellow-500 mr-2 text-xs"></i>
                                                        appearance_count
                                                    </li>
                                                    <li class="flex items-center">
                                                        <i class="fas fa-wave-square text-orange-500 mr-2 text-xs"></i>
                                                        momentum_phase
                                                    </li>
                                                    <li class="flex items-center">
                                                        <i class="fas fa-bell text-red-500 mr-2 text-xs"></i>
                                                        alerts
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="flex items-start">
                                                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold mr-2 mt-0.5">3</span>
                                                Store updated snapshot to database
                                            </li>
                                        </ol>
                                        <p class="text-gray-700 mt-3">This allows historical tracking even if the engine restarts.</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Early Detection Section -->
                        <section id="early-detection" class="bg-white rounded-xl shadow-lg overflow-hidden mt-8">
                            <div class="bg-gradient-to-r from-green-600 to-emerald-500 p-6">
                                <h2 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-bolt mr-3"></i>
                                    Early Detection Engine
                                </h2>
                                <p class="text-green-100 mt-2">Why this engine detects coins before they become top gainers</p>
                            </div>
                            
                            <div class="p-6 space-y-6">
                                <!-- Introduction -->
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-5">
                                    <h3 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                                        <i class="fas fa-binoculars mr-2"></i>
                                        The Timing Advantage
                                    </h3>
                                    <p class="text-gray-700">Traditional exchanges show top gainers <strong>after</strong> the pump. Our engine detects micro-signals <strong>before</strong> public visibility, giving you the timing advantage.</p>
                                </div>

                                <!-- Detection Methods -->
                                <div class="space-y-4">
                                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 mt-1 flex-shrink-0">
                                                <i class="fas fa-search-plus text-purple-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800 mb-2">1. Focus on Micro-Movements Before Public Visibility</h4>
                                                <p class="text-gray-700 mb-3">Typical exchanges list top gainers <strong>after the large pump has already occurred</strong>. This engine works in the opposite way: it analyzes <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">micro-signals</span> that appear long before a coin reaches the top gainer list.</p>
                                                <ul class="text-gray-700 space-y-1 text-sm">
                                                    <li class="flex items-center">
                                                        <i class="fas fa-arrow-up text-green-500 mr-2 text-xs"></i>
                                                        Small undetected price acceleration
                                                    </li>
                                                    <li class="flex items-center">
                                                        <i class="fas fa-volume-up text-blue-500 mr-2 text-xs"></i>
                                                        Micro-volume expansion
                                                    </li>
                                                    <li class="flex items-center">
                                                        <i class="fas fa-wave-square text-purple-500 mr-2 text-xs"></i>
                                                        Subtle RSI divergence
                                                    </li>
                                                    <li class="flex items-center">
                                                        <i class="fas fa-chart-line text-orange-500 mr-2 text-xs"></i>
                                                        Early trend forming candles
                                                    </li>
                                                    <li class="flex items-center">
                                                        <i class="fas fa-compress-alt text-red-500 mr-2 text-xs"></i>
                                                        Initial volatility compression breakouts
                                                    </li>
                                                </ul>
                                                <p class="text-gray-700 mt-3 text-sm">These small patterns are the "first footprints" before a coin performs a big run.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 mt-1 flex-shrink-0">
                                                <i class="fas fa-chart-bar text-blue-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800 mb-2">2. Smart-Money Volume Profiling Reveals Early Accumulation</h4>
                                                <p class="text-gray-700 mb-3">Your engine uses advanced multi-window volume metrics such as:</p>
                                                <div class="flex flex-wrap gap-2 mb-3">
                                                    <code class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-mono">volume_spike_ratio</code>
                                                    <code class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-mono">volume_acceleration</code>
                                                    <code class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-mono">volume_consistency</code>
                                                    <code class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-mono">volume_surge</code>
                                                </div>
                                                <p class="text-gray-700">These allow the engine to detect <strong>gradual accumulation behavior</strong> often executed by institutional or smart-money buyers before a breakout.</p>
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 mt-2">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    The engine catches volume footprints that humans usually miss
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 mt-1 flex-shrink-0">
                                                <i class="fas fa-wave-square text-purple-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800 mb-2">3. Momentum Phase Detection Identifies Early Trend Shifts</h4>
                                                <p class="text-gray-700 mb-3">The engine classifies coins using momentum phases:</p>
                                                <div class="flex flex-wrap gap-2 mb-3">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                                        <i class="fas fa-seedling mr-1"></i>ACCUMULATION
                                                    </span>
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-rocket mr-1"></i>ACCELERATION
                                                    </span>
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-fire mr-1"></i>PARABOLIC
                                                    </span>
                                                </div>
                                                <p class="text-gray-700">By detecting the first two phases (<em>Accumulation</em> &amp; <em>Acceleration</em>), the engine gives signals <strong>before</strong> a coin enters the Top Gainer zone.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 mt-1 flex-shrink-0">
                                                <i class="fas fa-redo text-green-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800 mb-2">4. Appearance Tracking Confirms Early Trend Strength</h4>
                                                <p class="text-gray-700 mb-3">This engine uses <code class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-mono">appearance_count</code> to confirm if a coin repeatedly appears in the screener.</p>
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                                    <div class="text-center p-3 bg-green-50 rounded border border-green-200">
                                                        <div class="text-2xl font-bold text-green-700">1–2</div>
                                                        <div class="text-sm text-green-600">early discovery</div>
                                                    </div>
                                                    <div class="text-center p-3 bg-blue-50 rounded border border-blue-200">
                                                        <div class="text-2xl font-bold text-blue-700">3–5</div>
                                                        <div class="text-sm text-blue-600">emerging trend</div>
                                                    </div>
                                                    <div class="text-center p-3 bg-purple-50 rounded border border-purple-200">
                                                        <div class="text-2xl font-bold text-purple-700">5+</div>
                                                        <div class="text-sm text-purple-600">strong consistent trend</div>
                                                    </div>
                                                </div>
                                                <p class="text-gray-700">This filtering ensures that early detections are not noise but part of a real underlying move.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4 mt-1 flex-shrink-0">
                                                <i class="fas fa-trending-up text-yellow-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800 mb-2">5. Score Progression (Improvement) Validates Growing Strength</h4>
                                                <p class="text-gray-700 mb-3">The engine measures:</p>
                                                <div class="bg-gray-800 rounded p-3 mb-3">
                                                    <code class="text-green-400 font-mono">score_improvement = current_score - first_score</code>
                                                </div>
                                                <p class="text-gray-700 mb-3">This allows the engine to identify coins whose strength is <strong>increasing over time</strong>, even if they haven't pumped hard yet.</p>
                                                <div class="flex gap-2">
                                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                                        <i class="fas fa-arrow-up mr-1"></i>Growing score = increasing buyer interest
                                                    </div>
                                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-minus mr-1"></i>Stagnant score = low breakout probability
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4 mt-1 flex-shrink-0">
                                                <i class="fas fa-layer-group text-red-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800 mb-2">6. Trend Strength + Health Score Reveal Early Hidden Winners</h4>
                                                <p class="text-gray-700 mb-3">The engine combines:</p>
                                                <div class="flex flex-wrap gap-2 mb-3">
                                                    <code class="bg-red-100 text-red-800 px-3 py-1 rounded text-sm font-mono">trend_strength</code>
                                                    <code class="bg-green-100 text-green-800 px-3 py-1 rounded text-sm font-mono">health_score</code>
                                                    <code class="bg-purple-100 text-purple-800 px-3 py-1 rounded text-sm font-mono">smart_confidence</code>
                                                </div>
                                                <p class="text-gray-700 mb-3">Together, these form a picture of:</p>
                                                <div class="bg-yellow-100 border border-yellow-200 rounded-lg p-3 mb-3">
                                                    <p class="text-yellow-800 font-semibold text-center">"Is this coin silently building strength before it explodes?"</p>
                                                </div>
                                                <p class="text-gray-700">If the answer is yes, the engine highlights it early.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4 mt-1 flex-shrink-0">
                                                <i class="fas fa-layer-group text-indigo-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800 mb-2">7. Order Block & Support Detection Spots Early Reversal Zones</h4>
                                                <p class="text-gray-700 mb-3">This engine checks:</p>
                                                <div class="flex flex-wrap gap-2 mb-3">
                                                    <code class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded text-sm font-mono">order_block_alignment</code>
                                                    <code class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded text-sm font-mono">order_block_score</code>
                                                </div>
                                                <p class="text-gray-700">If a coin is near a strong demand zone (support), this increases the probability of an early reversal and upward breakout.</p>
                                                <p class="text-gray-700 mt-2">These signals often appear <strong>before the broader market notices the move</strong>.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4 mt-1 flex-shrink-0">
                                                <i class="fas fa-globe text-orange-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800 mb-2">8. Global Market Context Adjustment Avoids False Early Signals</h4>
                                                <p class="text-gray-700 mb-3">This engine uses:</p>
                                                <code class="bg-orange-100 text-orange-800 px-3 py-1 rounded text-sm font-mono mb-3 inline-block">market_context_score</code>
                                                <p class="text-gray-700">Weak global conditions are filtered out, ensuring that a coin must show *genuine strength*, not just noise.</p>
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 mt-2">
                                                    <i class="fas fa-gem mr-1"></i>
                                                    Strong coin in weak market = early hidden gem
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mr-4 mt-1 flex-shrink-0">
                                                <i class="fas fa-chart-pie text-pink-600 text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800 mb-2">9. Top Sectors Dashboard Ranking</h4>
                                                <p class="text-gray-700 mb-3">This engine uses:</p>
                                                <div class="flex flex-wrap gap-2 mb-3">
                                                    <code class="bg-pink-100 text-pink-800 px-3 py-1 rounded text-sm font-mono">sectors top money inflow</code>
                                                    <code class="bg-pink-100 text-pink-800 px-3 py-1 rounded text-sm font-mono">sectors top change 24h</code>
                                                </div>
                                                <p class="text-gray-700">This provides additional information on sectors or narratives that are trending.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Final Summary -->
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-5">
                                    <h3 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                                        <i class="fas fa-flag-checkered mr-2"></i>
                                        Final Summary
                                    </h3>
                                    <p class="text-gray-700 mb-4">This engine is intentionally designed to detect coins <strong>before they reach the Top Gainer list</strong> because it analyzes:</p>
                                    <ul class="text-gray-700 space-y-2 mb-4">
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            Early micro-volatility patterns
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            Smart money accumulation signals
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            Accelerating score & trend strength
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            Consistent multi-appearance tracking
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            Momentum lifecycle phases
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            Support/order-block-based early reversals
                                        </li>
                                    </ul>
                                    <div class="bg-yellow-100 border border-yellow-200 rounded-lg p-4">
                                        <p class="text-yellow-800 font-semibold text-center"><strong>In short: the engine identifies coins while they are still "quiet", before they explode into public visibility.</strong></p>
                                    </div>
                                    <p class="text-gray-700 mt-3">This gives traders a significant timing advantage compared to waiting for the exchange's top gainer list, which always arrives too late.</p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                @include('layouts.footers.auth.footer')
            </div>
        </main>
    @else
        @include('layouts.navbars.guest.nav')
        <div class="w-full px-6 py-6 mx-auto">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800">Engine Documentation</h1>
                <p class="text-gray-600 mt-2">Please login to view documentation</p>
            </div>
        </div>
        @include('layouts.footers.guest.footer')
    @endauth

    @push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }
        
        section {
            scroll-margin-top: 100px;
        }
        
        code {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        }
        
        pre {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            line-height: 1.4;
        }
        
        .sticky {
            position: sticky;
        }
        
        /* Custom scrollbar for code blocks */
        pre::-webkit-scrollbar {
            height: 8px;
        }
        
        pre::-webkit-scrollbar-track {
            background: #1a202c;
            border-radius: 4px;
        }
        
        pre::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 4px;
        }
        
        pre::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                const offset = 100;
                const elementPosition = element.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }

        // Update active navigation
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('nav a');

            function updateActiveNav() {
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop - 150;
                    if (window.scrollY >= sectionTop) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.classList.remove('bg-purple-50', 'text-purple-700', 'bg-blue-50', 'text-blue-700', 'bg-green-50', 'text-green-700');
                    const href = link.getAttribute('href');
                    if (href === `#${current}`) {
                        if (href.includes('ai-signals') || href.includes('quick-summary') || href.includes('example-signal') || href.includes('field-explanation') || href.includes('priority-system') || href.includes('decision-rules') || href.includes('code-reference') || href.includes('troubleshooting') || href.includes('glossary')) {
                            link.classList.add('bg-purple-50', 'text-purple-700');
                        } else if (href.includes('performance')) {
                            link.classList.add('bg-blue-50', 'text-blue-700');
                        } else if (href.includes('early-detection')) {
                            link.classList.add('bg-green-50', 'text-green-700');
                        }
                    }
                });
            }

            window.addEventListener('scroll', updateActiveNav);
            updateActiveNav(); // Initial call
        });

        // Add copy functionality for code blocks
        document.addEventListener('DOMContentLoaded', function() {
            const codeBlocks = document.querySelectorAll('pre');
            codeBlocks.forEach(block => {
                block.addEventListener('click', function() {
                    const text = this.innerText;
                    navigator.clipboard.writeText(text).then(() => {
                        const original = this.innerHTML;
                        this.innerHTML = '<span style="color: #10B981;">✓ Copied to clipboard!</span>';
                        setTimeout(() => {
                            this.innerHTML = original;
                        }, 2000);
                    });
                });
                
                // Add hover effect
                block.style.cursor = 'pointer';
                block.title = 'Click to copy';
            });
        });
    </script>
    @endpush
</x-layouts.base>