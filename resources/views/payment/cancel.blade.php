<!-- resources/views/payment/cancel.blade.php -->
<x-layouts.base>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <i class="fas fa-times-circle text-red-600 text-2xl"></i>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Cancelled</h2>
            <p class="text-gray-600 mb-6">
                Your payment was cancelled or failed to process.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('premium.pricing') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-redo mr-2"></i>
                    Try Again
                </a>
                
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-layouts.base>