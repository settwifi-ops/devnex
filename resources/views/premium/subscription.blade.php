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

                        <!-- Payment Instructions Modal -->
                        <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                            <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-xl font-bold text-gray-900">Complete Your Payment</h3>
                                    <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times text-lg"></i>
                                    </button>
                                </div>
                                <div id="paymentContent">
                                    <!-- Payment instructions will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <!-- Credit Card Payment Modal -->
                        <div id="creditCardModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-xl font-bold text-gray-900">Credit Card Payment</h3>
                                    <button onclick="closeCreditCardModal()" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times text-lg"></i>
                                    </button>
                                </div>
                                
                                <!-- Credit Card Form yang Sederhana -->
                                <div id="creditCardContent">
                                    <div id="creditCardHeader" class="text-center mb-6">
                                        <!-- Header akan diisi secara dinamis -->
                                    </div>

                                    <div class="bg-blue-50 rounded-lg p-4 mb-6">
                                        <div class="flex items-center justify-center mb-3">
                                            <i class="fas fa-credit-card text-blue-500 text-2xl mr-3"></i>
                                            <div>
                                                <div class="font-semibold text-blue-800">Secure Credit Card Payment</div>
                                                <div class="text-sm text-blue-600">Auto-detect Visa/Mastercard</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Credit Card Icons -->
                                        <div class="flex justify-center space-x-4 mt-3">
                                            <div class="w-10 h-6 bg-blue-600 rounded flex items-center justify-center">
                                                <span class="text-white text-xs font-bold">VISA</span>
                                            </div>
                                            <div class="w-10 h-6 bg-red-600 rounded flex items-center justify-center">
                                                <span class="text-white text-xs font-bold">MC</span>
                                            </div>
                                            <div class="w-10 h-6 bg-orange-500 rounded flex items-center justify-center">
                                                <span class="text-white text-xs font-bold">JCB</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Security Badges -->
                                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                        <div class="flex items-center justify-center space-x-6">
                                            <div class="flex items-center">
                                                <i class="fas fa-lock text-green-500 mr-2"></i>
                                                <span class="text-xs text-gray-600">SSL Secure</span>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                                                <span class="text-xs text-gray-600">3D Secure</span>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-user-shield text-purple-500 mr-2"></i>
                                                <span class="text-xs text-gray-600">PCI DSS</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center text-sm text-gray-600 mb-6">
                                        <p>Your card will be automatically detected as Visa, Mastercard, or JCB</p>
                                        <p class="text-xs text-gray-500 mt-1">Supported by all major Indonesian banks</p>
                                    </div>

                                    <button type="button" 
                                            id="creditCardSubmit"
                                            onclick="processCreditCardPayment()"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 px-6 rounded-lg font-semibold text-lg transition duration-200 shadow-lg">
                                        <i class="fas fa-lock mr-2"></i>Proceed to Secure Payment
                                    </button>

                                    <div class="text-center mt-4">
                                        <p class="text-xs text-gray-500">
                                            You will be redirected to secure payment page
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Plans -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            
                            <!-- MONTHLY PLAN -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition duration-300">
                                <div class="text-center mb-5">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Monthly</h3>
                                    <div class="flex items-baseline justify-center mb-2">
                                        <span class="text-3xl font-bold text-blue-600">$17.99</span>
                                    </div>
                                    <p class="text-gray-500 text-sm">≈ Rp 299.000</p>
                                    <p class="text-gray-400 text-xs mt-1">Billed monthly</p>
                                </div>

                                <!-- FEATURES -->
                                <ul class="space-y-2.5 mb-5">
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700">Full Premium Access</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700">Real-time Signals</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700">AI Analysis Tools</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700">Priority Support</span>
                                    </li>
                                </ul>

                                <!-- PAYMENT METHOD SELECTION -->
                                <div class="mb-5">
                                    <label class="block text-xs font-semibold text-gray-700 mb-2">Payment Method:</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition duration-200 payment-option" data-plan="monthly">
                                            <input type="radio" name="payment_method_monthly" value="qris" class="mr-2 text-blue-600" checked>
                                            <i class="fas fa-qrcode text-blue-500 mr-2 text-sm"></i>
                                            <span class="font-medium text-sm">QRIS</span>
                                            <span class="ml-auto text-xs bg-green-100 text-green-800 px-1.5 py-0.5 rounded">Instant</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition duration-200 payment-option" data-plan="monthly">
                                            <input type="radio" name="payment_method_monthly" value="va" class="mr-2 text-blue-600">
                                            <i class="fas fa-university text-purple-500 mr-2 text-sm"></i>
                                            <span class="font-medium text-sm">Virtual Account</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition duration-200 payment-option" data-plan="monthly">
                                            <input type="radio" name="payment_method_monthly" value="credit_card" class="mr-2 text-blue-600">
                                            <i class="fas fa-credit-card text-orange-500 mr-2 text-sm"></i>
                                            <span class="font-medium text-sm">Credit Card</span>
                                        </label>
                                    </div>
                                </div>

                                <button type="button" 
                                        onclick="processPayment('monthly')"
                                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-3 px-4 rounded-lg font-semibold text-sm transition duration-200 shadow-sm payment-btn monthly-btn">
                                    <i class="fas fa-bolt mr-1"></i>Get Started
                                </button>
                            </div>

                            <!-- 6 MONTHS PLAN -->
                            <div class="bg-white rounded-xl shadow-md border-2 border-green-500 p-5 relative">
                                <div class="absolute -top-2 left-1/2 transform -translate-x-1/2">
                                    <span class="bg-gradient-to-r from-green-500 to-green-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                        ⭐ MOST POPULAR
                                    </span>
                                </div>
                                
                                <div class="text-center mb-5">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">6 Months</h3>
                                    <div class="flex items-baseline justify-center mb-2">
                                        <span class="text-3xl font-bold text-green-600">$89.99</span>
                                    </div>
                                    <p class="text-gray-500 text-sm">≈ Rp 1.497.000</p>
                                    <p class="text-green-600 font-semibold text-xs mt-1">Save 15%</p>
                                </div>

                                <!-- FEATURES -->
                                <ul class="space-y-2.5 mb-5">
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700">Everything in Monthly</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-star text-yellow-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700 font-medium">Advanced Analytics</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-star text-yellow-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700 font-medium">Portfolio Optimization</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-crown text-purple-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700 font-medium">VIP Support</span>
                                    </li>
                                </ul>

                                <!-- PAYMENT METHOD SELECTION -->
                                <div class="mb-5">
                                    <label class="block text-xs font-semibold text-gray-700 mb-2">Payment Method:</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-green-50 cursor-pointer transition duration-200 payment-option" data-plan="6months">
                                            <input type="radio" name="payment_method_6months" value="qris" class="mr-2 text-green-600" checked>
                                            <i class="fas fa-qrcode text-green-500 mr-2 text-sm"></i>
                                            <span class="font-medium text-sm">QRIS</span>
                                            <span class="ml-auto text-xs bg-green-100 text-green-800 px-1.5 py-0.5 rounded">Instant</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-green-50 cursor-pointer transition duration-200 payment-option" data-plan="6months">
                                            <input type="radio" name="payment_method_6months" value="va" class="mr-2 text-green-600">
                                            <i class="fas fa-university text-purple-500 mr-2 text-sm"></i>
                                            <span class="font-medium text-sm">Virtual Account</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-green-50 cursor-pointer transition duration-200 payment-option" data-plan="6months">
                                            <input type="radio" name="payment_method_6months" value="credit_card" class="mr-2 text-green-600">
                                            <i class="fas fa-credit-card text-orange-500 mr-2 text-sm"></i>
                                            <span class="font-medium text-sm">Credit Card</span>
                                        </label>
                                    </div>
                                </div>

                                <button type="button" 
                                        onclick="processPayment('6months')"
                                        class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white py-3 px-4 rounded-lg font-semibold text-sm transition duration-200 shadow-sm payment-btn sixmonths-btn">
                                    <i class="fas fa-rocket mr-1"></i>Get Premium
                                </button>
                            </div>

                            <!-- YEARLY PLAN -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition duration-300">
                                <div class="text-center mb-5">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Yearly</h3>
                                    <div class="flex items-baseline justify-center mb-2">
                                        <span class="text-3xl font-bold text-purple-600">$149.99</span>
                                    </div>
                                    <p class="text-gray-500 text-sm">≈ Rp 2.508.000</p>
                                    <p class="text-purple-600 font-semibold text-xs mt-1">Save 30%</p>
                                </div>

                                <!-- FEATURES -->
                                <ul class="space-y-2.5 mb-5">
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700">Everything in 6 Months</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-gem text-purple-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700 font-medium">Custom Indicators</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-gem text-purple-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700 font-medium">Dedicated Account Manager</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <i class="fas fa-gift text-red-500 mr-2 text-xs"></i>
                                        <span class="text-gray-700 font-medium">Early Access to New Features</span>
                                    </li>
                                </ul>

                                <!-- PAYMENT METHOD SELECTION -->
                                <div class="mb-5">
                                    <label class="block text-xs font-semibold text-gray-700 mb-2">Payment Method:</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-purple-50 cursor-pointer transition duration-200 payment-option" data-plan="yearly">
                                            <input type="radio" name="payment_method_yearly" value="qris" class="mr-2 text-purple-600" checked>
                                            <i class="fas fa-qrcode text-purple-500 mr-2 text-sm"></i>
                                            <span class="font-medium text-sm">QRIS</span>
                                            <span class="ml-auto text-xs bg-green-100 text-green-800 px-1.5 py-0.5 rounded">Instant</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-purple-50 cursor-pointer transition duration-200 payment-option" data-plan="yearly">
                                            <input type="radio" name="payment_method_yearly" value="va" class="mr-2 text-purple-600">
                                            <i class="fas fa-university text-blue-500 mr-2 text-sm"></i>
                                            <span class="font-medium text-sm">Virtual Account</span>
                                        </label>
                                        <label class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-purple-50 cursor-pointer transition duration-200 payment-option" data-plan="yearly">
                                            <input type="radio" name="payment_method_yearly" value="credit_card" class="mr-2 text-purple-600">
                                            <i class="fas fa-credit-card text-orange-500 mr-2 text-sm"></i>
                                            <span class="font-medium text-sm">Credit Card</span>
                                        </label>
                                    </div>
                                </div>

                                <button type="button" 
                                        onclick="processPayment('yearly')"
                                        class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white py-3 px-4 rounded-lg font-semibold text-sm transition duration-200 shadow-sm payment-btn yearly-btn">
                                    <i class="fas fa-crown mr-1"></i>Go Pro
                                </button>
                            </div>
                        </div>

                        <!-- Payment Security Notice -->
                        <div class="mt-6 text-center">
                            <div class="inline-flex items-center text-xs text-gray-500 bg-gray-50 px-4 py-2 rounded-lg">
                                <i class="fas fa-shield-alt text-green-500 mr-2"></i>
                                <span>Secure payment processed by Midtrans. Your financial information is encrypted and protected.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentOrderId = null;
        let paymentCheckInterval = null;
        let currentPaymentMethod = null;
        let currentPlanType = null;
        let currentAmount = null;

        const planPrices = {
            'monthly': 299000,
            '6months': 1497000,
            'yearly': 2508000
        };

        function processPayment(planType) {
            console.log('Processing payment for:', planType);
            
            // Get selected payment method
            const paymentMethod = document.querySelector(`input[name="payment_method_${planType}"]:checked`).value;
            console.log('Selected payment method:', paymentMethod);
            
            currentPaymentMethod = paymentMethod;
            currentPlanType = planType;
            currentAmount = planPrices[planType];

            if (paymentMethod === 'credit_card') {
                showCreditCardModal(planType);
            } else {
                processDirectPayment(planType, paymentMethod);
            }
        }

        function processDirectPayment(planType, paymentMethod) {
            // Show loading state on button
            const button = document.querySelector(`.${planType}-btn`);
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';

            // AJAX request
            fetch('{{ route("payment.subscribe") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    plan: planType,
                    payment_method: paymentMethod
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
                
                if (data.success) {
                    currentOrderId = data.payment_data.order_id;
                    showPaymentInstructions(data.payment_data);
                } else {
                    alert('Error: ' + data.message);
                    resetButton(button, originalText);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error: ' + error.message);
                resetButton(button, originalText);
            });
        }

        function showCreditCardModal(planType) {
            const modal = document.getElementById('creditCardModal');
            const header = document.getElementById('creditCardHeader');
            const submitButton = document.getElementById('creditCardSubmit');
            
            const amount = planPrices[planType];
            const formattedAmount = 'Rp ' + amount.toLocaleString('id-ID');
            
            header.innerHTML = `
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-credit-card text-blue-600 text-2xl"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-1">Credit Card Payment</h4>
                <p class="text-gray-600 text-sm">Amount: ${formattedAmount}</p>
                <p class="text-gray-500 text-xs">Secure 3D Secure payment</p>
            `;
            
            submitButton.innerHTML = `<i class="fas fa-lock mr-2"></i>Pay ${formattedAmount}`;
            
            modal.classList.remove('hidden');
        }

        function processCreditCardPayment() {
            // Show loading
            const button = document.getElementById('creditCardSubmit');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

            // Kirim request payment tanpa parameter bank
            fetch('{{ route("payment.subscribe") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    plan: currentPlanType,
                    payment_method: 'credit_card'
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Credit Card response:', data);
                
                if (data.success) {
                    currentOrderId = data.payment_data.order_id;
                    
                    // Untuk credit card dengan Snap token
                    if (data.payment_data.snap_token) {
                        openSnapPopup(data.payment_data.snap_token);
                    } 
                    // Jika ada redirect URL (Core API)
                    else if (data.payment_data.payment_data?.redirect_url) {
                        window.location.href = data.payment_data.payment_data.redirect_url;
                    }
                    // Fallback - show payment instructions
                    else {
                        closeCreditCardModal();
                        showPaymentInstructions(data.payment_data);
                    }
                } else {
                    alert('Error: ' + data.message);
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error: ' + error.message);
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        function openSnapPopup(snapToken) {
            // Buka Snap popup untuk credit card
            window.snap.pay(snapToken, {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    showPaymentSuccess();
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    showPaymentPending();
                },
                onError: function(result) {
                    console.log('Payment error:', result);
                    showPaymentError();
                },
                onClose: function() {
                    console.log('Payment popup closed');
                    // User closed the popup, reset button
                    const button = document.getElementById('creditCardSubmit');
                    button.disabled = false;
                    button.innerHTML = `<i class="fas fa-lock mr-2"></i>Pay Rp ${currentAmount.toLocaleString('id-ID')}`;
                }
            });
        }

        function showPaymentInstructions(paymentData) {
            const modal = document.getElementById('paymentModal');
            const content = document.getElementById('paymentContent');
            
            const instructions = paymentData.payment_instructions;
            let html = '';
            
            // Header
            html += `
                <div class="mb-6">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-4 rounded-lg text-center mb-4">
                        <h4 class="font-bold text-lg mb-2">Payment Instructions</h4>
                        <p class="text-blue-100">Order ID: ${paymentData.order_id}</p>
                        <p class="text-blue-100 font-semibold">Amount: Rp ${paymentData.amount.toLocaleString('id-ID')}</p>
                    </div>
                </div>
            `;
            
            // Payment-specific instructions
            if (instructions.type === 'credit_card') {
                // Credit Card - Show Snap payment button
                html += `
                    <div class="text-center mb-6">
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200 mb-4">
                            <i class="fas fa-credit-card text-blue-500 text-4xl mb-3"></i>
                            <div class="text-lg font-semibold text-blue-800">Credit Card Payment</div>
                            <div class="text-blue-600 text-sm">Secure 3D Secure Payment</div>
                        </div>
                        <div class="space-y-3 text-left mb-6">
                            ${instructions.instructions.map(instruction => `
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                    <span class="text-gray-700 text-sm">${instruction}</span>
                                </div>
                            `).join('')}
                        </div>
                        <button onclick="openSnapPopup('${instructions.snap_token}')" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 px-6 rounded-lg font-semibold text-lg transition duration-200 shadow-lg mb-4">
                            <i class="fas fa-lock mr-2"></i>Complete Payment
                        </button>
                    </div>
                `;
            } else {
                // QRIS & Virtual Account
                switch(instructions.type) {
                    case 'qris':
                        html += `
                            <div class="text-center mb-6">
                                <div class="bg-white p-4 rounded-lg border-2 border-dashed border-gray-300 inline-block mb-4">
                                    <img src="${instructions.qr_code_url}" alt="QR Code" class="w-48 h-48 mx-auto">
                                </div>
                                <div class="space-y-3 text-left">
                                    ${instructions.instructions.map(instruction => `
                                        <div class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                            <span class="text-gray-700 text-sm">${instruction}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-yellow-600 mr-2"></i>
                                    <span class="text-yellow-800 text-sm font-medium">Payment expires in 24 hours</span>
                                </div>
                            </div>
                        `;
                        break;
                        
                    case 'va':
                        html += `
                            <div class="text-center mb-6">
                                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-4">
                                    <div class="text-2xl font-mono font-bold text-gray-900 tracking-wider mb-2">
                                        ${instructions.va_number}
                                    </div>
                                    <div class="text-sm text-gray-600">Virtual Account Number</div>
                                    <div class="text-xs text-gray-500 mt-1">Bank: ${instructions.bank}</div>
                                </div>
                                <div class="space-y-3 text-left">
                                    ${instructions.instructions.map(instruction => `
                                        <div class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                            <span class="text-gray-700 text-sm">${instruction}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                        break;
                }
            }
            
            // Common footer (hide for credit card as we have Snap button)
            if (instructions.type !== 'credit_card') {
                html += `
                    <div class="flex space-x-3 mt-6">
                        <button onclick="closePaymentModal()" 
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg font-medium transition duration-200">
                            Cancel
                        </button>
                        <button onclick="startPaymentStatusCheck()" 
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-medium transition duration-200">
                            <i class="fas fa-sync-alt mr-2"></i>Check Status
                        </button>
                    </div>
                `;
            }
            
            html += `<div id="paymentStatus" class="mt-4 hidden"></div>`;
            
            content.innerHTML = html;
            modal.classList.remove('hidden');
            
            // Auto start status check for non-credit card payments
            if (currentPaymentMethod !== 'credit_card') {
                startPaymentStatusCheck();
            }
        }

        function showPaymentSuccess() {
            const statusDiv = document.getElementById('paymentStatus');
            if (statusDiv) {
                statusDiv.classList.remove('hidden');
                statusDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                        <div class="text-green-800 font-semibold">Payment Successful!</div>
                        <div class="text-green-600 text-sm mt-1">Redirecting to dashboard...</div>
                    </div>
                `;
            }
            
            setTimeout(() => {
                window.location.href = '{{ route("payment.finish") }}?status_code=200&order_id=' + currentOrderId;
            }, 2000);
            
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
            }
        }

        function showPaymentPending() {
            const statusDiv = document.getElementById('paymentStatus');
            if (statusDiv) {
                statusDiv.classList.remove('hidden');
                statusDiv.innerHTML = `
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <i class="fas fa-clock text-yellow-500 text-2xl mb-2"></i>
                        <div class="text-yellow-800 font-semibold">Payment Pending</div>
                        <div class="text-yellow-600 text-sm mt-1">Waiting for payment confirmation...</div>
                    </div>
                `;
            }
            startPaymentStatusCheck();
        }

        function showPaymentError() {
            const statusDiv = document.getElementById('paymentStatus');
            if (statusDiv) {
                statusDiv.classList.remove('hidden');
                statusDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <i class="fas fa-times-circle text-red-500 text-2xl mb-2"></i>
                        <div class="text-red-800 font-semibold">Payment Failed</div>
                        <div class="text-red-600 text-sm mt-1">Please try again or contact support.</div>
                    </div>
                `;
            }
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('hidden');
            
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
                paymentCheckInterval = null;
            }
            
            // Reset all buttons
            resetAllButtons();
        }

        function closeCreditCardModal() {
            const modal = document.getElementById('creditCardModal');
            modal.classList.add('hidden');
            resetAllButtons();
        }

        function resetAllButtons() {
            document.querySelectorAll('.payment-btn').forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = btn.getAttribute('data-original-text');
            });
        }

        function resetButton(button, originalText) {
            button.disabled = false;
            button.innerHTML = originalText;
        }

        function startPaymentStatusCheck() {
            if (!currentOrderId) return;
            
            const statusDiv = document.getElementById('paymentStatus');
            if (statusDiv) {
                statusDiv.classList.remove('hidden');
                statusDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin text-blue-500 mr-2"></i>Checking payment status...</div>';
            }
            
            // Check immediately
            checkPaymentStatus();
            
            // Then check every 10 seconds
            paymentCheckInterval = setInterval(checkPaymentStatus, 10000);
        }

        function checkPaymentStatus() {
            if (!currentOrderId) return;
            
            fetch('{{ route("payment.check-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    order_id: currentOrderId
                })
            })
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById('paymentStatus');
                if (!statusDiv) return;
                
                if (data.success) {
                    if (data.transaction_status === 'settlement' || data.transaction_status === 'capture') {
                        statusDiv.innerHTML = `
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                                <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                                <div class="text-green-800 font-semibold">Payment Successful!</div>
                                <div class="text-green-600 text-sm mt-1">Redirecting to dashboard...</div>
                            </div>
                        `;
                        
                        setTimeout(() => {
                            window.location.href = '{{ route("payment.finish") }}?status_code=200&order_id=' + currentOrderId;
                        }, 2000);
                        
                        if (paymentCheckInterval) {
                            clearInterval(paymentCheckInterval);
                        }
                    } else if (data.transaction_status === 'pending') {
                        statusDiv.innerHTML = `
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                <i class="fas fa-clock text-yellow-500 text-2xl mb-2"></i>
                                <div class="text-yellow-800 font-semibold">Payment Pending</div>
                                <div class="text-yellow-600 text-sm mt-1">Waiting for payment confirmation...</div>
                            </div>
                        `;
                    } else {
                        statusDiv.innerHTML = `
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                                <i class="fas fa-times-circle text-red-500 text-2xl mb-2"></i>
                                <div class="text-red-800 font-semibold">Payment ${data.transaction_status}</div>
                                <div class="text-red-600 text-sm mt-1">Please try again or contact support.</div>
                            </div>
                        `;
                    }
                } else {
                    statusDiv.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <div class="text-red-800 font-semibold">Failed to check status</div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Status check error:', error);
            });
        }

        // Initialize payment options
        document.addEventListener('DOMContentLoaded', function() {
            // Store original button text
            document.querySelectorAll('.payment-btn').forEach(btn => {
                btn.setAttribute('data-original-text', btn.innerHTML);
            });

            // Set first payment option as active for each plan
            ['monthly', '6months', 'yearly'].forEach(plan => {
                const firstOption = document.querySelector(`input[name="payment_method_${plan}"]`);
                if (firstOption) {
                    firstOption.checked = true;
                    firstOption.closest('label').classList.add('border-blue-500', 'bg-blue-50');
                }
            });

            // Add click handlers to payment options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        // Remove active class from siblings
                        const groupName = radio.getAttribute('name');
                        document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
                            r.closest('label').classList.remove('border-blue-500', 'bg-blue-50', 'border-green-500', 'bg-green-50', 'border-purple-500', 'bg-purple-50');
                        });
                        
                        // Add active class to clicked option with appropriate color
                        const planType = groupName.replace('payment_method_', '');
                        if (planType === 'monthly') {
                            this.classList.add('border-blue-500', 'bg-blue-50');
                        } else if (planType === '6months') {
                            this.classList.add('border-green-500', 'bg-green-50');
                        } else {
                            this.classList.add('border-purple-500', 'bg-purple-50');
                        }
                        
                        radio.checked = true;
                    }
                });
            });

            // Load Midtrans Snap.js if needed
            @if(config('services.midtrans.is_production', false))
                const script = document.createElement('script');
                script.src = 'https://app.midtrans.com/snap/snap.js';
                script.setAttribute('data-client-key', '{{ config('services.midtrans.client_key') }}');
                document.head.appendChild(script);
            @else
                const script = document.createElement('script');
                script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
                script.setAttribute('data-client-key', '{{ config('services.midtrans.client_key') }}');
                document.head.appendChild(script);
            @endif
        });
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</x-layouts.base>