<x-layouts.base>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Complete Your Payment</h2>
            <p class="text-gray-600 mb-6">You are subscribing to: <strong class="text-blue-600">{{ ucfirst($plan) }} Plan</strong></p>
            
            <div id="snap-container" class="my-8">
                <div class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-300 rounded-lg">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                    <p class="text-gray-500">Loading secure payment gateway...</p>
                </div>
            </div>

            <!-- Manual Debug Buttons -->
            <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-700 mb-2">Debug Tools (Development Only):</p>
                <div class="space-x-2">
                    <button onclick="simulateSuccess()" class="bg-green-600 text-white px-4 py-2 rounded text-sm">
                        Simulate Success
                    </button>
                    <button onclick="simulateFailed()" class="bg-red-600 text-white px-4 py-2 rounded text-sm">
                        Simulate Failed
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Midtrans Snap JS -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" 
            data-client-key="{{ $client_key }}"></script>
    
    <script>
        console.log('=== MIDTRANS PAYMENT INITIALIZATION ===');
        console.log('Plan: {{ $plan }}');
        console.log('Client Key: {{ $client_key }}');

        // Initialize Snap payment
        snap.pay('{{ $snap_token }}', {
            onSuccess: function(result) {
                console.log('💰 PAYMENT SUCCESS:', result);
                // Redirect dengan semua parameter yang diperlukan
                window.location.href = '{{ url("/payment/finish") }}?status_code=200&order_id=' + result.order_id + '&transaction_id=' + result.transaction_id;
            },
            onPending: function(result) {
                console.log('⏳ PAYMENT PENDING:', result);
                window.location.href = '{{ url("/payment/finish") }}?status_code=201&order_id=' + result.order_id;
            },
            onError: function(result) {
                console.log('❌ PAYMENT ERROR:', result);
                window.location.href = '{{ url("/payment/finish") }}?status_code=400&order_id=' + (result.order_id || 'unknown');
            },
            onClose: function() {
                console.log('🚪 PAYMENT POPUP CLOSED');
                window.location.href = '{{ route("payment.cancel") }}';
            }
        });

        // Debug functions
        function simulateSuccess() {
            window.location.href = '{{ url("/payment/finish") }}?status_code=200&order_id=MID-TEST-' + Date.now();
        }

        function simulateFailed() {
            window.location.href = '{{ url("/payment/finish") }}?status_code=400&order_id=MID-TEST-' + Date.now();
        }
    </script>
</x-layouts.base>