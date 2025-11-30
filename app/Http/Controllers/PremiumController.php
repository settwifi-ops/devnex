<?php
namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PremiumController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function subscription()
    {
        Log::info('Subscription page accessed', ['user_id' => auth()->id()]);

        // Di PremiumController - sesuaikan jika ada
        $plans = [
            'monthly' => [
                'name' => 'Monthly',
                'price_display' => 'Rp 490.000',  // Display IDR
                'price_idr' => 490000,            // Amount untuk Midtrans
                'duration_days' => 30,
            ],
            '6months' => [
                'name' => '6 Months', 
                'price_display' => 'Rp 2.500.000',
                'price_idr' => 2500000, 
                'duration_days' => 180,
                'discount' => 15
            ],
            'yearly' => [
                'name' => 'Yearly',
                'price_display' => 'Rp 4.850.000',
                'price_idr' => 4850000,
                'duration_days' => 365,
                'discount' => 30
            ]
        ];

        return view('premium.subscription', [
            'plans' => $plans,
            'currency' => 'USD',
            'user' => Auth::user()
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        try {
            $user = Auth::user();
            $user->update($request->only(['name', 'email']));

            Log::info('Profile updated via subscription page', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);

            return back()->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            Log::error('Profile update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to update profile.');
        }
    }

    /**
     * Process payment - DELEGATE KE PAYMENTCONTROLLER
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:monthly,6months,yearly',
            'payment_method' => 'required|in:qris,va,credit_card'
        ]);

        try {
            $user = Auth::user();
            $plan = $request->plan;
            $paymentMethod = $request->payment_method;

            Log::info('PremiumController delegating payment to PaymentController', [
                'user_id' => $user->id,
                'plan' => $plan,
                'payment_method' => $paymentMethod
            ]);

            // Check if user already has active subscription
            if ($user->hasActivePremium()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription.'
                ], 400);
            }

            // Create instance of PaymentController dan panggil createSubscription
            $paymentController = app(PaymentController::class);
            return $paymentController->createSubscription($request);

        } catch (\Exception $e) {
            Log::error('Payment delegation failed', [
                'user_id' => Auth::id(),
                'plan' => $request->plan,
                'payment_method' => $request->payment_method,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage()
            ], 500);
        }
    }
}