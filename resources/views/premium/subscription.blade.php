<x-layouts.base>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Navigation Header -->
        <nav class="bg-white shadow-sm border-b">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <!-- Logo/Brand -->
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">TradingPro</span>
                    </div>

                    <!-- User Actions -->
                    <div class="flex items-center space-x-4">
                        <!-- Dashboard Button -->
                        @if(auth()->user()->hasActiveTrial() || auth()->user()->hasActivePremium())
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                        @else
                            <div class="flex items-center px-4 py-2 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed" 
                                 title="Upgrade to premium to access dashboard">
                                <i class="fas fa-lock mr-2"></i>Dashboard
                            </div>
                        @endif
                        
                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="py-8">
            <div class="container mx-auto px-4 max-w-7xl">
                
                <!-- Status & Profile Section -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                    <!-- User Profile Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sticky top-8">
                            <div class="flex items-center mb-5">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-lg">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-3">
                                    <h3 class="font-semibold text-gray-900 text-sm">{{ auth()->user()->name }}</h3>
                                    <p class="text-xs text-gray-500 truncate max-w-[150px]">{{ auth()->user()->email }}</p>
                                </div>
                            </div>

                            <!-- Subscription Status -->
                            <div class="mb-5">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Subscription Status</span>
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 rounded-full bg-{{ auth()->user()->hasActiveTrial() ? 'yellow' : (auth()->user()->hasActivePremium() ? 'green' : 'gray') }}-500 mr-1"></div>
                                        <span class="text-xs font-medium text-gray-600">{{ auth()->user()->getReadableStatus() }}</span>
                                    </div>
                                </div>
                                
                                @if(auth()->user()->hasActiveTrial())
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium text-yellow-800">Trial Period</span>
                                            <span class="text-xs font-bold text-yellow-700">{{ auth()->user()->getRemainingTrialDays() }} days left</span>
                                        </div>
                                        <div class="w-full bg-yellow-200 rounded-full h-1.5 mt-2">
                                            <div class="bg-yellow-500 h-1.5 rounded-full" style="width: {{ (auth()->user()->getRemainingTrialDays() / 14) * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Profile Update Form -->
                            <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-user-edit text-blue-500 mr-2 text-sm"></i>
                                Profile Settings
                            </h4>
                            
                            <form id="profileForm" action="{{ route('subscription.update-profile') }}" method="POST">
                                @csrf
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Full Name</label>
                                        <input type="text" name="name" value="{{ auth()->user()->name }}" 
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                               placeholder="Your full name">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                                        <input type="email" name="email" value="{{ auth()->user()->email }}" 
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                               placeholder="Your email">
                                    </div>
                                    
                                    <button type="submit" 
                                            class="w-full bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white py-2 px-4 rounded-lg font-medium text-sm transition duration-200 shadow-sm">
                                        <i class="fas fa-save mr-1"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Upgrade Message & Plans -->
                    <div class="lg:col-span-3">
                        <!-- Upgrade Message for Non-Premium Users -->
                        @if(!auth()->user()->hasActiveTrial() && !auth()->user()->hasActivePremium())
                            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-xl p-5 mb-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-bold text-yellow-800 mb-1">Premium Access Required</h3>
                                        <p class="text-yellow-700 text-sm">
                                            You need an active premium subscription to access the dashboard features. 
                                            Choose a plan below to unlock all premium trading tools and analytics.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Exchange Rate Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-sync-alt text-blue-500 mr-2"></i>
                                    <span class="text-sm font-medium text-blue-800">Real-time Exchange Rate</span>
                                </div>
                                <div id="exchangeRateInfo" class="text-xs text-blue-600">
                                    Loading USD to IDR rate...
                                </div>
                            </div>
                        </div>

                        <!-- Loading Modal -->
                        <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                            <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 text-center">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Processing Payment</h3>
                                <p class="text-gray-600 text-sm">Please wait while we initialize your payment...</p>
                            </div>
                        </div>

                        <!-- Pricing Plans -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <!-- MONTHLY PLAN -->
                            <div class="bg-white rounded-2xl shadow-xl border border-blue-200 overflow-hidden transform hover:scale-105 transition duration-300">
                                <div class="p-6">
                                    <div class="text-center mb-6">
                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full mb-4">
                                            STARTER
                                        </span>
                                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Monthly</h3>
                                        <div class="flex items-baseline justify-center mb-2">
                                            <span class="text-4xl font-bold text-blue-600" id="monthlyUsd">$32</span>
                                        </div>
                                        <p class="text-gray-600 font-medium">Rp 490.000</p>
                                        <p class="text-gray-400 text-sm mt-1">Billed monthly</p>
                                    </div>

                                    <!-- FEATURES -->
                                    <ul class="space-y-3 mb-6">
                                        <li class="flex items-center">
                                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                            <span class="text-gray-700">Real-time Trading Signals</span>
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                            <span class="text-gray-700">Advanced Chart Analysis</span>
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                            <span class="text-gray-700">AI Market Predictions</span>
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                            <span class="text-gray-700">Priority Support</span>
                                        </li>
                                    </ul>

                                    <button type="button" 
                                            onclick="processPayment('monthly')"
                                            class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 transform hover:scale-105 shadow-lg payment-btn monthly-btn">
                                        <i class="fas fa-bolt mr-2"></i>Get Started
                                    </button>
                                </div>
                            </div>

                            <!-- 6 MONTHS PLAN -->
                            <div class="bg-white rounded-2xl shadow-2xl border-2 border-green-500 relative transform hover:scale-105 transition duration-300">
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                                    <span class="bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-bold px-4 py-2 rounded-full shadow-lg">
                                        ⭐ MOST POPULAR
                                    </span>
                                </div>
                                
                                <div class="p-6 pt-10">
                                    <div class="text-center mb-6">
                                        <h3 class="text-2xl font-bold text-gray-900 mb-3">6 Months</h3>
                                        <div class="flex items-baseline justify-center mb-2">
                                            <span class="text-4xl font-bold text-green-600" id="sixmonthsUsd">$155</span>
                                        </div>
                                        <p class="text-gray-600 font-medium">Rp 2.500.000</p>
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-2 mt-3">
                                            <span class="text-green-700 font-semibold text-sm">Save 15%</span>
                                        </div>
                                    </div>

                                    <!-- FEATURES -->
                                    <ul class="space-y-3 mb-6">
                                        <li class="flex items-center">
                                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                            <span class="text-gray-700 font-semibold">Everything in Monthly</span>
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-star text-yellow-500 mr-3"></i>
                                            <span class="text-gray-700 font-semibold">Portfolio Management</span>
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-star text-yellow-500 mr-3"></i>
                                            <span class="text-gray-700 font-semibold">Risk Analysis Tools</span>
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-crown text-purple-500 mr-3"></i>
                                            <span class="text-gray-700 font-semibold">VIP Support Channel</span>
                                        </li>
                                    </ul>

                                    <button type="button" 
                                            onclick="processPayment('6months')"
                                            class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 transform hover:scale-105 shadow-lg payment-btn sixmonths-btn">
                                        <i class="fas fa-rocket mr-2"></i>Get Premium
                                    </button>
                                </div>
                            </div>

                            <!-- YEARLY PLAN -->
                            <div class="bg-white rounded-2xl shadow-xl border border-purple-200 overflow-hidden transform hover:scale-105 transition duration-300">
                                <div class="p-6">
                                    <div class="text-center mb-6">
                                        <span class="inline-block bg-purple-100 text-purple-800 text-xs font-semibold px-3 py-1 rounded-full mb-4">
                                            PRO
                                        </span>
                                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Yearly</h3>
                                        <div class="flex items-baseline justify-center mb-2">
                                            <span class="text-4xl font-bold text-purple-600" id="yearlyUsd">$300</span>
                                        </div>
                                        <p class="text-gray-600 font-medium">Rp 4.850.000</p>
                                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-2 mt-3">
                                            <span class="text-purple-700 font-semibold text-sm">Save 30%</span>
                                        </div>
                                    </div>

                                    <!-- FEATURES -->
                                    <ul class="space-y-3 mb-6">
                                        <li class="flex items-center">
                                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                            <span class="text-gray-700 font-semibold">Everything in 6 Months</span>
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-gem text-purple-500 mr-3"></i>
                                            <span class="text-gray-700 font-semibold">Custom Indicators</span>
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-gem text-purple-500 mr-3"></i>
                                            <span class="text-gray-700 font-semibold">Dedicated Account Manager</span>
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-gift text-red-500 mr-3"></i>
                                            <span class="text-gray-700 font-semibold">Early Feature Access</span>
                                        </li>
                                    </ul>

                                    <button type="button" 
                                            onclick="processPayment('yearly')"
                                            class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl transition duration-200 transform hover:scale-105 shadow-lg payment-btn yearly-btn">
                                        <i class="fas fa-crown mr-2"></i>Go Pro
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Features Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                            <div class="text-center p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-bolt text-blue-600 text-xl"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Lightning Fast</h4>
                                <p class="text-gray-600 text-sm">Real-time data with minimal latency</p>
                            </div>

                            <div class="text-center p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-shield-alt text-green-600 text-xl"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Secure & Reliable</h4>
                                <p class="text-gray-600 text-sm">Bank-level security guaranteed</p>
                            </div>

                            <div class="text-center p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Advanced Analytics</h4>
                                <p class="text-gray-600 text-sm">AI-powered insights and predictions</p>
                            </div>
                        </div>

                        <!-- Payment Security Notice -->
                        <div class="mt-8 text-center">
                            <div class="inline-flex items-center bg-gray-50 rounded-2xl px-6 py-4 border border-gray-200">
                                <i class="fas fa-lock text-green-600 text-xl mr-3"></i>
                                <div class="text-left">
                                    <h4 class="font-semibold text-gray-900 text-sm">Secure Payment Processing</h4>
                                    <p class="text-xs text-gray-600">All payments are encrypted and processed securely via Midtrans</p>
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

        // Harga dalam IDR (tanpa titik) - HARGA BARU
        const planPrices = {
            'monthly': 490000,      // 490 ribu
            '6months': 2500000,     // 2.5 juta
            'yearly': 4850000       // 4.85 juta
        };

        // Harga USD fixed (dengan rate 1 USD = 15,300 IDR)
        const usdPrices = {
            'monthly': 32,
            '6months': 155,
            'yearly': 300
        };

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
            alert('Payment successful! Redirecting to dashboard...');
            setTimeout(() => {
                window.location.href = '{{ route("payment.finish") }}?status_code=200&order_id=' + currentOrderId;
            }, 1000);
        }

        // Show pending message
        function showPendingMessage() {
            alert('Payment is pending. Please complete the payment process. You will be notified once payment is confirmed.');
            resetAllButtons();
        }

        // Show error message
        function showErrorMessage(message) {
            alert(message || 'Payment failed. Please try again.');
            resetAllButtons();
        }

        // Get USD to IDR exchange rate (simulated)
        async function getExchangeRate() {
            // Simulate API call with fixed rate
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve(15300); // 1 USD = 15,300 IDR
                }, 500);
            });
        }

        // Update exchange rate info
        async function updateExchangeRate() {
            try {
                const rate = await getExchangeRate();
                document.getElementById('exchangeRateInfo').innerHTML = 
                    `1 USD = Rp ${rate.toLocaleString('id-ID')}`;
            } catch (error) {
                console.error('Exchange rate error:', error);
                document.getElementById('exchangeRateInfo').innerHTML = 
                    '1 USD ≈ Rp 15,300';
            }
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

            // Update exchange rate
            updateExchangeRate();

            // Load Midtrans Snap.js
            loadMidtransScript();
        });

        // Load Midtrans Snap script
        function loadMidtransScript() {
            // Cek apakah script sudah dimuat
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