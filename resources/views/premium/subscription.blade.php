<x-layouts.base>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-100/30">
        <!-- Navigation Header -->
        <nav class="bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-200/60">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <!-- Logo/Brand -->
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-700 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-chart-line text-white text-lg"></i>
                        </div>
                        <div>
                            <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">TradingPro</span>
                            <p class="text-xs text-gray-500">Premium Subscription</p>
                        </div>
                    </div>

                    <!-- User Actions -->
                    <div class="flex items-center space-x-4">
                        <!-- Dashboard Button -->
                        @if(auth()->user()->hasActiveTrial() || auth()->user()->hasActivePremium())
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                        @else
                            <div class="flex items-center px-6 py-2.5 bg-gray-400 text-white font-semibold rounded-xl cursor-not-allowed shadow-sm" 
                                 title="Upgrade to premium to access dashboard">
                                <i class="fas fa-lock mr-2"></i>Dashboard
                            </div>
                        @endif
                        
                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="flex items-center px-6 py-2.5 bg-gradient-to-r from-gray-600 to-slate-700 hover:from-gray-700 hover:to-slate-800 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="py-12">
            <div class="container mx-auto px-4 max-w-7xl">
                
                <!-- Status & Profile Section -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-12">
                    <!-- User Profile Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-gray-200/50 p-6 sticky top-8">
                            <!-- User Avatar & Info -->
                            <div class="text-center mb-6">
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <span class="text-white font-bold text-2xl">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                                <h3 class="font-bold text-gray-900 text-lg mb-1">{{ auth()->user()->name }}</h3>
                                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                            </div>

                            <!-- Subscription Status -->
                            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200/50">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-gray-700">Status</span>
                                    <div class="flex items-center">
                                        <div class="w-2.5 h-2.5 rounded-full bg-{{ auth()->user()->hasActiveTrial() ? 'yellow' : (auth()->user()->hasActivePremium() ? 'green' : 'gray') }}-500 mr-2 animate-pulse"></div>
                                        <span class="text-xs font-bold text-gray-600">{{ auth()->user()->getReadableStatus() }}</span>
                                    </div>
                                </div>
                                
                                @if(auth()->user()->hasActiveTrial())
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-xs">
                                            <span class="font-medium text-yellow-700">Trial Period</span>
                                            <span class="font-bold text-yellow-700">{{ auth()->user()->getRemainingTrialDays() }} days left</span>
                                        </div>
                                        <div class="w-full bg-yellow-200 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-yellow-400 to-amber-500 h-2 rounded-full transition-all duration-1000" 
                                                 style="width: {{ (auth()->user()->getRemainingTrialDays() / 14) * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Profile Update Form -->
                            <div class="border-t border-gray-200/50 pt-6">
                                <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-user-cog text-blue-500 mr-3"></i>
                                    Profile Settings
                                </h4>
                                
                                <form id="profileForm" action="{{ route('subscription.update-profile') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                        <input type="text" name="name" value="{{ auth()->user()->name }}" 
                                               class="w-full px-4 py-3 text-sm border border-gray-300/80 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                               placeholder="Your full name">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                        <input type="email" name="email" value="{{ auth()->user()->email }}" 
                                               class="w-full px-4 py-3 text-sm border border-gray-300/80 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                               placeholder="Your email">
                                    </div>
                                    
                                    <button type="submit" 
                                            class="w-full bg-gradient-to-r from-gray-700 to-slate-800 hover:from-gray-800 hover:to-slate-900 text-white font-semibold py-3.5 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                        <i class="fas fa-save mr-2"></i>Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Upgrade Message & Plans -->
                    <div class="lg:col-span-3">
                        <!-- Exchange Rate Info -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/50 rounded-2xl p-4 mb-8 backdrop-blur-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-sync-alt text-blue-500 mr-3 text-lg"></i>
                                    <span class="text-sm font-semibold text-blue-800">Real-time Exchange Rate</span>
                                </div>
                                <div id="exchangeRateInfo" class="text-sm text-blue-600 font-medium">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading USD to IDR rate...
                                </div>
                            </div>
                        </div>

                        <!-- Upgrade Message for Non-Premium Users -->
                        @if(!auth()->user()->hasActiveTrial() && !auth()->user()->hasActivePremium())
                            <div class="bg-gradient-to-r from-amber-50 to-orange-50/80 border border-amber-200/60 rounded-2xl p-6 mb-8 backdrop-blur-sm">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                                            <i class="fas fa-crown text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-amber-900 mb-2">Unlock Premium Features</h3>
                                        <p class="text-amber-800/80 text-sm leading-relaxed">
                                            Elevate your trading experience with advanced tools, real-time analytics, and professional insights. 
                                            Choose your plan below and start your journey to successful trading.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Loading Modal -->
                        <div id="loadingModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden transition-opacity duration-300">
                            <div class="bg-white/90 backdrop-blur-md rounded-3xl p-8 max-w-md w-full mx-4 text-center transform transition-all duration-500 scale-95 animate-pulse">
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <i class="fas fa-spinner fa-spin text-white text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-3">Processing Payment</h3>
                                <p class="text-gray-600 mb-2">Initializing secure payment gateway...</p>
                                <p class="text-sm text-gray-500">You will be redirected shortly</p>
                            </div>
                        </div>

                        <!-- Pricing Plans -->
                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-12">
                            
                            <!-- MONTHLY PLAN -->
                            <div class="group relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-600/10 rounded-3xl blur-lg group-hover:blur-xl transition-all duration-500"></div>
                                <div class="relative bg-white/80 backdrop-blur-md rounded-3xl shadow-2xl border border-blue-200/30 overflow-hidden transform group-hover:scale-105 transition-all duration-500">
                                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400"></div>
                                    <div class="p-8">
                                        <div class="text-center mb-8">
                                            <span class="inline-block bg-blue-100 text-blue-800 text-sm font-bold px-4 py-2 rounded-full mb-4 shadow-sm">
                                                STARTER
                                            </span>
                                            <h3 class="text-3xl font-bold text-gray-900 mb-4">Monthly</h3>
                                            <div class="flex items-baseline justify-center mb-3">
                                                <span class="text-5xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent" id="monthlyUsd">-</span>
                                                <span class="text-gray-500 ml-2 text-lg">/month</span>
                                            </div>
                                            <p class="text-gray-600 font-semibold text-lg">Rp 490.000</p>
                                            <p class="text-gray-400 text-sm mt-2">Perfect for getting started</p>
                                        </div>

                                        <!-- FEATURES -->
                                        <ul class="space-y-4 mb-8">
                                            <li class="flex items-center">
                                                <i class="fas fa-check-circle text-green-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-medium">Real-time Trading Signals</span>
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-check-circle text-green-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-medium">Advanced Chart Analysis</span>
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-check-circle text-green-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-medium">AI Market Predictions</span>
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-check-circle text-green-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-medium">Priority Email Support</span>
                                            </li>
                                        </ul>

                                        <button type="button" 
                                                onclick="processPayment('monthly')"
                                                class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-300 shadow-2xl hover:shadow-3xl transform hover:scale-105 payment-btn monthly-btn group">
                                            <i class="fas fa-bolt mr-2 group-hover:animate-pulse"></i>Get Started Now
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 6 MONTHS PLAN - FEATURED -->
                            <div class="group relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500"></div>
                                <div class="relative bg-gradient-to-br from-white to-emerald-50/80 backdrop-blur-md rounded-3xl shadow-3xl border-2 border-emerald-400/50 overflow-hidden transform group-hover:scale-105 transition-all duration-500">
                                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                        <span class="bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-bold px-6 py-3 rounded-full shadow-2xl animate-pulse">
                                            ⭐ MOST POPULAR
                                        </span>
                                    </div>
                                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-green-500 to-emerald-400"></div>
                                    
                                    <div class="p-8 pt-12">
                                        <div class="text-center mb-8">
                                            <span class="inline-block bg-emerald-100 text-emerald-800 text-sm font-bold px-4 py-2 rounded-full mb-4 shadow-sm">
                                                PROFESSIONAL
                                            </span>
                                            <h3 class="text-3xl font-bold text-gray-900 mb-4">6 Months</h3>
                                            <div class="flex items-baseline justify-center mb-3">
                                                <span class="text-5xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent" id="6monthsUsd">-</span>
                                                <span class="text-gray-500 ml-2 text-lg">/6 months</span>
                                            </div>
                                            <p class="text-gray-600 font-semibold text-lg">Rp 2.485.000</p>
                                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-emerald-200 rounded-2xl p-3 mt-4">
                                                <span class="text-emerald-700 font-bold text-sm">💰 Save 20% vs Monthly</span>
                                            </div>
                                        </div>

                                        <!-- FEATURES -->
                                        <ul class="space-y-4 mb-8">
                                            <li class="flex items-center">
                                                <i class="fas fa-check-circle text-emerald-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-bold">Everything in Monthly</span>
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-star text-yellow-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-bold">Portfolio Management</span>
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-star text-yellow-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-bold">Risk Analysis Tools</span>
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-crown text-purple-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-bold">VIP Support Channel</span>
                                            </li>
                                        </ul>

                                        <button type="button" 
                                                onclick="processPayment('6months')"
                                                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-300 shadow-2xl hover:shadow-3xl transform hover:scale-105 payment-btn 6months-btn group">
                                            <i class="fas fa-rocket mr-2 group-hover:animate-bounce"></i>Get Premium Access
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- YEARLY PLAN -->
                            <div class="group relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-600/10 rounded-3xl blur-lg group-hover:blur-xl transition-all duration-500"></div>
                                <div class="relative bg-white/80 backdrop-blur-md rounded-3xl shadow-2xl border border-purple-200/30 overflow-hidden transform group-hover:scale-105 transition-all duration-500">
                                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-400"></div>
                                    <div class="p-8">
                                        <div class="text-center mb-8">
                                            <span class="inline-block bg-purple-100 text-purple-800 text-sm font-bold px-4 py-2 rounded-full mb-4 shadow-sm">
                                                ENTERPRISE
                                            </span>
                                            <h3 class="text-3xl font-bold text-gray-900 mb-4">Yearly</h3>
                                            <div class="flex items-baseline justify-center mb-3">
                                                <span class="text-5xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent" id="yearlyUsd">-</span>
                                                <span class="text-gray-500 ml-2 text-lg">/year</span>
                                            </div>
                                            <p class="text-gray-600 font-semibold text-lg">Rp 4.850.000</p>
                                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-2xl p-3 mt-4">
                                                <span class="text-purple-700 font-bold text-sm">🎯 Save 35% vs Monthly</span>
                                            </div>
                                        </div>

                                        <!-- FEATURES -->
                                        <ul class="space-y-4 mb-8">
                                            <li class="flex items-center">
                                                <i class="fas fa-check-circle text-purple-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-bold">Everything in Professional</span>
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-gem text-purple-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-bold">Custom Indicators</span>
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-gem text-purple-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-bold">Dedicated Account Manager</span>
                                            </li>
                                            <li class="flex items-center">
                                                <i class="fas fa-gift text-pink-500 text-lg mr-4"></i>
                                                <span class="text-gray-700 font-bold">Early Feature Access</span>
                                            </li>
                                        </ul>

                                        <button type="button" 
                                                onclick="processPayment('yearly')"
                                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-300 shadow-2xl hover:shadow-3xl transform hover:scale-105 payment-btn yearly-btn group">
                                            <i class="fas fa-crown mr-2 group-hover:animate-pulse"></i>Go Pro Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Features Showcase -->
                        <div class="bg-gradient-to-br from-white to-blue-50/30 rounded-3xl shadow-2xl border border-gray-200/50 p-12 backdrop-blur-sm">
                            <div class="text-center mb-12">
                                <h2 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-4">
                                    Why Traders Choose Us
                                </h2>
                                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                                    Join thousands of successful traders who trust our platform for their trading journey
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                                <div class="text-center group">
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-bolt text-white text-xl"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">Lightning Fast</h4>
                                    <p class="text-gray-600 text-sm">Real-time data with sub-second latency</p>
                                </div>

                                <div class="text-center group">
                                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-shield-alt text-white text-xl"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">Bank-Level Security</h4>
                                    <p class="text-gray-600 text-sm">Military-grade encryption & protection</p>
                                </div>

                                <div class="text-center group">
                                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-brain text-white text-xl"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">AI Powered</h4>
                                    <p class="text-gray-600 text-sm">Advanced machine learning algorithms</p>
                                </div>

                                <div class="text-center group">
                                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-headset text-white text-xl"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">24/7 Support</h4>
                                    <p class="text-gray-600 text-sm">Dedicated support team always ready</p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Security -->
                        <div class="text-center mt-12">
                            <div class="inline-flex items-center bg-gradient-to-r from-gray-50 to-slate-100 rounded-2xl px-8 py-6 border border-gray-200/60 shadow-lg backdrop-blur-sm">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                                    <i class="fas fa-lock text-white text-lg"></i>
                                </div>
                                <div class="text-left">
                                    <h4 class="font-bold text-gray-900 text-lg">Secure Payment Processing</h4>
                                    <p class="text-gray-600 text-sm">All payments are encrypted and processed securely via Midtrans</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentOrderId = null;
        let currentPlanType = null;
        let exchangeRate = null;

        // Harga dalam IDR (tanpa titik)
        const planPrices = {
            'monthly': 490000,      // 490 ribu
            '6months': 2485000,     // 2.5 juta
            'yearly': 4850000       // 4.85 juta
        };

        // Get real exchange rate from API
        async function getExchangeRate() {
            try {
                console.log('🌐 Fetching real exchange rate...');
                
                // Gunakan API gratis dari ExchangeRate-API
                const response = await fetch('https://api.exchangerate-api.com/v4/latest/USD');
                
                if (!response.ok) {
                    throw new Error('Failed to fetch exchange rate');
                }
                
                const data = await response.json();
                const usdToIdr = data.rates.IDR;
                
                console.log('💱 Real exchange rate:', usdToIdr);
                return usdToIdr;
                
            } catch (error) {
                console.error('❌ Exchange rate API error:', error);
                
                // Fallback: coba API lain (currency-api.com)
                try {
                    console.log('🔄 Trying fallback API...');
                    const fallbackResponse = await fetch('https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/usd.json');
                    const fallbackData = await fallbackResponse.json();
                    const fallbackRate = fallbackData.usd.idr;
                    console.log('💱 Fallback exchange rate:', fallbackRate);
                    return fallbackRate;
                } catch (fallbackError) {
                    console.error('❌ Fallback API also failed:', fallbackError);
                    
                    // Final fallback: fixed rate berdasarkan market
                    const fixedRate = 15600; // Rate reasonable berdasarkan market
                    console.log('🛠️ Using fixed rate:', fixedRate);
                    return fixedRate;
                }
            }
        }

        // Convert IDR to USD
        function convertIdrToUsd(idrAmount, rate) {
            return idrAmount / rate;
        }

        // Format USD currency
        function formatUsd(amount) {
            return '$' + amount.toFixed(2);
        }

        // Update USD prices based on real exchange rate
        async function updateUsdPrices() {
            try {
                const rate = await getExchangeRate();
                exchangeRate = rate;
                
                // Update exchange rate info
                document.getElementById('exchangeRateInfo').innerHTML = 
                    `1 USD = Rp ${Math.round(rate).toLocaleString('id-ID')}`;
                
                // Update prices for each plan
                updatePlanUsdPrice('monthly', planPrices.monthly, rate);
                updatePlanUsdPrice('6months', planPrices['6months'], rate);
                updatePlanUsdPrice('yearly', planPrices.yearly, rate);
                
                console.log('✅ USD prices updated with real exchange rate');
                
            } catch (error) {
                console.error('❌ Failed to update USD prices:', error);
                
                // Use reasonable fixed prices if API fails
                document.getElementById('exchangeRateInfo').innerHTML = 
                    '1 USD ≈ Rp 15,600 (Market Rate)';
                
                // Set reasonable USD prices based on market rate
                document.getElementById('monthlyUsd').textContent = '$31.41';
                document.getElementById('6months').textContent = '$160.26';
                document.getElementById('yearlyUsd').textContent = '$310.90';
            }
        }

        // Update individual plan USD price
        function updatePlanUsdPrice(planType, idrAmount, rate) {
            const usdAmount = convertIdrToUsd(idrAmount, rate);
            const usdElement = document.getElementById(planType + 'Usd');
            
            if (usdElement) {
                usdElement.textContent = formatUsd(usdAmount);
            }
        }

        // Main payment processing function
        function processPayment(planType) {
            console.log('🚀 Starting payment process for plan:', planType);
            
            currentPlanType = planType;

            // Show loading modal
            showLoadingModal();

            // Disable button
            const button = document.querySelector(`.${planType}-btn`);
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

            // AJAX request to get Snap token
            fetch('{{ route("payment.subscribe") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    plan: planType,
                    payment_method: 'snap'
                })
            })
            .then(response => {
                console.log('📥 Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('✅ Server response:', data);
                
                if (data.success) {
                    handlePaymentSuccess(data);
                } else {
                    throw new Error(data.message || 'Payment initialization failed');
                }
            })
            .catch(error => {
                console.error('❌ Payment error:', error);
                hideLoadingModal();
                alert('Error: ' + error.message);
                resetButton(button, originalText);
            });
        }

        // Handle successful payment response
        function handlePaymentSuccess(data) {
            console.log('🎯 Payment successful, opening Snap...');
            
            // Simpan order ID untuk callback
            currentOrderId = data.order_id;

            // Pastikan Snap token ada
            if (!data.snap_token) {
                throw new Error('No Snap token received');
            }

            console.log('🎫 Snap token received, opening popup...');
            
            // Buka Snap popup
            window.snap.pay(data.snap_token, {
                onSuccess: function(result) {
                    console.log('✅ Payment success:', result);
                    handleSnapCallback('success', result);
                },
                onPending: function(result) {
                    console.log('⏳ Payment pending:', result);
                    handleSnapCallback('pending', result);
                },
                onError: function(result) {
                    console.log('❌ Payment error:', result);
                    handleSnapCallback('error', result);
                },
                onClose: function() {
                    console.log('🔒 Payment popup closed by user');
                    hideLoadingModal();
                    resetAllButtons();
                }
            });
        }

        // Handle Snap callback
        function handleSnapCallback(status, result) {
            hideLoadingModal();
            
            switch(status) {
                case 'success':
                    showSuccessMessage();
                    break;
                case 'pending':
                    showPendingMessage();
                    break;
                case 'error':
                    showErrorMessage('Payment failed. Please try again.');
                    break;
            }
        }

        // Show success message and redirect
        function showSuccessMessage() {
            alert('🎉 Payment successful! Redirecting to dashboard...');
            setTimeout(() => {
                window.location.href = '{{ route("payment.finish") }}?status_code=200&order_id=' + currentOrderId;
            }, 1500);
        }

        // Show pending message
        function showPendingMessage() {
            alert('⏳ Payment is pending. Please complete the payment process. You will be notified once payment is confirmed.');
            resetAllButtons();
        }

        // Show error message
        function showErrorMessage(message) {
            alert('❌ ' + (message || 'Payment failed. Please try again.'));
            resetAllButtons();
        }

        // Modal functions
        function showLoadingModal() {
            document.getElementById('loadingModal').classList.remove('hidden');
        }

        function hideLoadingModal() {
            document.getElementById('loadingModal').classList.add('hidden');
        }

        // Utility functions
        function resetButton(button, originalText) {
            button.disabled = false;
            button.innerHTML = originalText;
        }

        function resetAllButtons() {
            document.querySelectorAll('.payment-btn').forEach(btn => {
                btn.disabled = false;
                if (btn.getAttribute('data-original-text')) {
                    btn.innerHTML = btn.getAttribute('data-original-text');
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Store original button text
            document.querySelectorAll('.payment-btn').forEach(btn => {
                btn.setAttribute('data-original-text', btn.innerHTML);
            });

            // Load real exchange rate and update prices
            updateUsdPrices();

            // Refresh exchange rate every 5 minutes
            setInterval(updateUsdPrices, 300000);

            // Load Midtrans Snap.js
            loadMidtransScript();
        });

        // Load Midtrans Snap script
        function loadMidtransScript() {
            if (window.snap) {
                console.log('📜 Midtrans Snap already loaded');
                return;
            }

            const script = document.createElement('script');
            const isProduction = {{ config('services.midtrans.is_production', false) ? 'true' : 'false' }};
            const clientKey = '{{ config('services.midtrans.client_key') }}';
            
            script.src = isProduction 
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js';
            
            script.setAttribute('data-client-key', clientKey);
            
            script.onload = function() {
                console.log('✅ Midtrans Snap loaded successfully');
            };
            
            script.onerror = function() {
                console.error('❌ Failed to load Midtrans Snap');
            };
            
            document.head.appendChild(script);
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</x-layouts.base>