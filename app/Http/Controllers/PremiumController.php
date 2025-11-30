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

        $plans = [
            'monthly' => [
                'name' => 'Monthly',
                'price_display' => '$17.99',
                'price_idr' => 299000,
                'duration_days' => 30,
            ],
            '6months' => [
                'name' => '6 Months', 
                'price_display' => '$89.99',
                'price_idr' => 1497000,
                'duration_days' => 180,
                'discount' => 15
            ],
            'yearly' => [
                'name' => 'Yearly',
                'price_display' => '$149.99',
                'price_idr' => 2508000, 
                'duration_days' => 365,
                'discount' => 30
            ]
        ];

        return view('premium.subscription', [ // Ganti dari pricing ke subscription
            'plans' => $plans,
            'currency' => 'USD',
            'user' => Auth::user() // Tambahkan user data
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
     * Process payment directly (ganti redirect ke Midtrans)
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

            Log::info('Payment process initiated', [
                'user_id' => $user->id,
                'plan' => $plan,
                'payment_method' => $paymentMethod
            ]);

            // TODO: Integrasi Midtrans langsung di sini
            $paymentResult = $this->processMidtransPayment($user, $plan, $paymentMethod);

            if ($paymentResult['success']) {
                // Payment berhasil - activate premium
                $this->activateUserPremium($user, $plan);
                
                Log::info('Payment successful', [
                    'user_id' => $user->id,
                    'plan' => $plan,
                    'payment_method' => $paymentMethod
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful! Premium activated.',
                    'redirect_url' => route('dashboard')
                ]);

            } else {
                Log::error('Payment failed', [
                    'user_id' => $user->id,
                    'plan' => $plan,
                    'payment_method' => $paymentMethod,
                    'error' => $paymentResult['message']
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $paymentResult['message'] ?? 'Payment failed. Please try again.'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment process error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'System error. Please try again later.'
            ], 500);
        }
    }

    /**
     * Process Midtrans payment (placeholder - sesuaikan dengan setup Midtrans Anda)
     */
    private function processMidtransPayment($user, $plan, $paymentMethod)
    {
        // PLAN MAPPING
        $planPrices = [
            'monthly' => 299000,
            '6months' => 1497000, 
            'yearly' => 2508000
        ];

        $planDurations = [
            'monthly' => 30,
            '6months' => 180,
            'yearly' => 365
        ];

        try {
            // TODO: Implement Midtrans API call berdasarkan payment method
            // Contoh untuk QRIS:
            if ($paymentMethod === 'qris') {
                // $midtransResponse = Midtrans::createQRISTransaction(...);
            }
            // Contoh untuk VA:
            elseif ($paymentMethod === 'va') {
                // $midtransResponse = Midtrans::createVATransaction(...);
            }
            // Contoh untuk Credit Card:
            elseif ($paymentMethod === 'credit_card') {
                // $midtransResponse = Midtrans::createCardTransaction(...);
            }

            // SIMULASI SUKSES (hapus ini ketika implementasi real)
            return [
                'success' => true,
                'transaction_id' => 'sim_' . time(),
                'payment_url' => '#', // URL untuk redirect jika needed
                'message' => 'Payment processed successfully'
            ];

            // UNCOMMENT UNTUK IMPLEMENTASI REAL:
            /*
            if ($midtransResponse->status_code === '201') {
                return [
                    'success' => true,
                    'transaction_id' => $midtransResponse->transaction_id,
                    'payment_url' => $midtransResponse->redirect_url,
                    'message' => 'Payment initiated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $midtransResponse->status_message ?? 'Payment failed'
                ];
            }
            */

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment gateway error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Activate premium for user setelah payment success
     */
    private function activateUserPremium($user, $plan)
    {
        $planDurations = [
            'monthly' => 30,
            '6months' => 180, 
            'yearly' => 365
        ];

        $premiumEndsAt = now()->addDays($planDurations[$plan]);

        $user->update([
            'subscription_tier' => 'premium',
            'premium_ends_at' => $premiumEndsAt,
            'trial_ends_at' => null // Hapus trial jika ada
        ]);

        Log::info('User premium activated', [
            'user_id' => $user->id,
            'plan' => $plan,
            'premium_ends_at' => $premiumEndsAt
        ]);
    }
}