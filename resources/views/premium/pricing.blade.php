<x-layouts.base>
    <div class="container mx-auto px-4 py-8">
        <!-- HEADER WITH USER STATUS -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold mb-2">Upgrade Your Account</h1>
            <div class="inline-block bg-{{ auth()->user()->hasActiveTrial() ? 'yellow' : 'gray' }}-100 
                       text-{{ auth()->user()->hasActiveTrial() ? 'yellow' : 'gray' }}-800 
                       px-4 py-2 rounded-full font-semibold">
                Status: {{ auth()->user()->getReadableStatus() }}
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            
            <!-- COLUMN 1: PROFILE SETTINGS -->
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-300 rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4">Profile Settings</h3>
                    
                    <form id="profileForm" action="{{ route('subscription.update-profile') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                            <input type="text" name="name" value="{{ auth()->user()->name }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" value="{{ auth()->user()->email }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 transition duration-200">
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- COLUMN 2 & 3: PRICING PLANS + PAYMENT -->
            <div class="lg:col-span-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Monthly Plan -->
                    <div class="bg-white border border-gray-300 rounded-lg p-6 text-center">
                        <h3 class="text-xl font-bold mb-4">Monthly</h3>
                        <div class="text-3xl font-bold text-blue-600 mb-4">$17.99</div>
                        <div class="text-sm text-gray-500 mb-4">≈ Rp 299.000</div>
                        
                        <!-- Payment Method Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method:</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method_monthly" value="qris" class="mr-2" checked>
                                    <span class="text-sm">QRIS</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method_monthly" value="va" class="mr-2">
                                    <span class="text-sm">Virtual Account</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method_monthly" value="credit_card" class="mr-2">
                                    <span class="text-sm">Credit Card</span>
                                </label>
                            </div>
                        </div>
                        
                        <button type="button" 
                                onclick="processPayment('monthly', 'monthly')"
                                class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 font-semibold transition duration-200">
                            Pay Now - Monthly
                        </button>
                    </div>

                    <!-- 6 Months Plan -->
                    <div class="bg-white border border-gray-300 rounded-lg p-6 text-center relative">
                        <div class="absolute top-4 right-4">
                            <span class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">
                                Save 15%
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-bold mb-4">6 Months</h3>
                        <div class="text-3xl font-bold text-blue-600 mb-4">$89.99</div>
                        <div class="text-sm text-gray-500 mb-4">≈ Rp 1.497.000</div>
                        
                        <!-- Payment Method Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method:</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method_6months" value="qris" class="mr-2" checked>
                                    <span class="text-sm">QRIS</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method_6months" value="va" class="mr-2">
                                    <span class="text-sm">Virtual Account</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method_6months" value="credit_card" class="mr-2">
                                    <span class="text-sm">Credit Card</span>
                                </label>
                            </div>
                        </div>
                        
                        <button type="button" 
                                onclick="processPayment('6months', '6months')"
                                class="w-full bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 font-semibold transition duration-200">
                            Pay Now - 6 Months
                        </button>
                    </div>

                    <!-- Yearly Plan -->
                    <div class="bg-white border border-gray-300 rounded-lg p-6 text-center relative">
                        <div class="absolute top-4 right-4">
                            <span class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">
                                Save 30%
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-bold mb-4">Yearly</h3>
                        <div class="text-3xl font-bold text-blue-600 mb-4">$149.99</div>
                        <div class="text-sm text-gray-500 mb-4">≈ Rp 2.508.000</div>
                        
                        <!-- Payment Method Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method:</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method_yearly" value="qris" class="mr-2" checked>
                                    <span class="text-sm">QRIS</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method_yearly" value="va" class="mr-2">
                                    <span class="text-sm">Virtual Account</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method_yearly" value="credit_card" class="mr-2">
                                    <span class="text-sm">Credit Card</span>
                                </label>
                            </div>
                        </div>
                        
                        <button type="button" 
                                onclick="processPayment('yearly', 'yearly')"
                                class="w-full bg-purple-600 text-white py-3 px-6 rounded-lg hover:bg-purple-700 font-semibold transition duration-200">
                            Pay Now - Yearly
                        </button>
                    </div>
                </div>

                <!-- PAYMENT RESULT SECTION (Initially Hidden) -->
                <div id="paymentResult" class="mt-6 hidden">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
    function processPayment(planType, planId) {
        // Get selected payment method
        const paymentMethod = document.querySelector(`input[name="payment_method_${planType}"]:checked`).value;
        
        // Show loading
        document.getElementById('paymentResult').innerHTML = `
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-blue-800">Processing payment...</p>
            </div>
        `;
        document.getElementById('paymentResult').classList.remove('hidden');

        // Send AJAX request
        fetch('{{ route("subscription.process-payment") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                plan: planId,
                payment_method: paymentMethod
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Payment success - show success message
                document.getElementById('paymentResult').innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                        <div class="text-green-600 text-4xl mb-4">✓</div>
                        <h3 class="text-xl font-bold text-green-800 mb-2">Payment Successful!</h3>
                        <p class="text-green-600 mb-4">You will be redirected to dashboard in <span id="countdown">5</span> seconds</p>
                        <a href="{{ route('dashboard') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                            Go to Dashboard Now
                        </a>
                    </div>
                `;
                
                // Auto redirect countdown
                let countdown = 5;
                const countdownElement = document.getElementById('countdown');
                const countdownInterval = setInterval(() => {
                    countdown--;
                    countdownElement.textContent = countdown;
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = '{{ route("dashboard") }}';
                    }
                }, 1000);
                
            } else {
                // Payment failed
                document.getElementById('paymentResult').innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                        <div class="text-red-600 text-4xl mb-4">✗</div>
                        <h3 class="text-xl font-bold text-red-800 mb-2">Payment Failed</h3>
                        <p class="text-red-600 mb-4">${data.message || 'Please try again'}</p>
                        <button onclick="document.getElementById('paymentResult').classList.add('hidden')" 
                                class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                            Try Again
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('paymentResult').innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                    <div class="text-red-600 text-4xl mb-4">✗</div>
                    <h3 class="text-xl font-bold text-red-800 mb-2">Network Error</h3>
                    <p class="text-red-600 mb-4">Please check your connection and try again</p>
                    <button onclick="document.getElementById('paymentResult').classList.add('hidden')" 
                            class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                        Try Again
                    </button>
                </div>
            `;
        });
    }
    </script>
</x-layouts.base>