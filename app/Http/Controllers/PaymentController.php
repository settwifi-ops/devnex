<?php
namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\User;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Create subscription payment
     */
    public function createSubscription(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:monthly,6months,yearly',
            'payment_method' => 'required|in:qris,va,credit_card'
        ]);

        $user = auth()->user();
        $plan = $request->plan;
        $paymentMethod = $request->payment_method;

        \Log::info('=== PAYMENT SUBSCRIBE REQUEST ===', [
            'user_id' => $user->id,
            'plan' => $plan,
            'payment_method' => $paymentMethod,
            'is_ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson()
        ]);

        // Check if user already has active subscription
        if ($user->hasActivePremium()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription.'
            ], 400);
        }

        try {
            $paymentData = $this->midtransService->createSubscription($user, $plan, $paymentMethod);
            
            \Log::info('Payment data generated successfully', [
                'order_id' => $paymentData['order_id'],
                'user_id' => $user->id,
                'payment_method' => $paymentMethod,
                'has_snap_token' => isset($paymentData['snap_token']),
                'has_payment_data' => isset($paymentData['payment_data'])
            ]);

            return response()->json([
                'success' => true,
                'payment_data' => $paymentData,
                'order_id' => $paymentData['order_id'],
                'message' => 'Payment initialized successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Payment initialization failed: ' . $e->getMessage());
            \Log::error('Payment error details:', [
                'user_id' => $user->id,
                'plan' => $plan,
                'payment_method' => $paymentMethod,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment finish callback
     */
    public function paymentFinish(Request $request)
    {
        \Log::info('=== PAYMENT FINISH CALLED ===', [
            'all_query_params' => $request->all(),
            'full_url' => $request->fullUrl(),
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name
        ]);

        // Get parameters from both GET and POST
        $status = $request->query('status_code') ?? $request->input('status_code');
        $orderId = $request->query('order_id') ?? $request->input('order_id');
        $transactionId = $request->query('transaction_id') ?? $request->input('transaction_id');

        \Log::info('Payment finish parameters extracted:', [
            'status' => $status,
            'order_id' => $orderId,
            'transaction_id' => $transactionId
        ]);

        // Jika semua parameter NULL, mungkin user langsung akses /payment/finish tanpa melalui Midtrans
        if (!$status && !$orderId && !$transactionId) {
            \Log::warning('Payment finish accessed directly without parameters');
            
            return redirect()->route('subscription')
                ->with('info', 'Please complete your payment process first.');
        }

        // If no order_id but has transaction_id, use transaction_id as order_id
        if (!$orderId && $transactionId) {
            $orderId = $transactionId;
            \Log::info('Using transaction_id as order_id:', ['order_id' => $orderId]);
        }

        if ($status === '200' && $orderId) {
            return $this->handleSuccessfulPayment($orderId);
        } else {
            return $this->handleFailedPayment($status, $orderId);
        }
    }

    /**
     * Handle successful payment
     */
    private function handleSuccessfulPayment($orderId)
    {
        \Log::info('Handling successful payment', ['order_id' => $orderId]);

        // Check if subscription exists
        $subscription = Subscription::where('subscription_id', $orderId)->first();

        if ($subscription) {
            \Log::info('Existing subscription found:', [
                'subscription_id' => $subscription->id,
                'current_status' => $subscription->status,
                'order_id' => $orderId
            ]);

            if ($subscription->status === 'active') {
                // Already active via webhook
                return $this->redirectToDashboard('success', 'Payment successful! Premium subscription activated.');
            } else {
                // Activate manually if still pending
                return $this->activateSubscriptionManually($subscription);
            }
        } else {
            // Create new subscription if not exists
            return $this->createNewSubscription($orderId);
        }
    }

    /**
     * Activate subscription manually
     */
    private function activateSubscriptionManually($subscription)
    {
        \Log::info('Activating subscription manually', ['subscription_id' => $subscription->id]);

        // Determine end date based on plan
        $endDate = $this->getPlanEndDate($subscription->plan);

        // Update subscription
        $subscription->update([
            'status' => 'active',
            'start_date' => now(),
            'end_date' => $endDate,
        ]);

        // Update user
        $user = $subscription->user;
        $user->update([
            'subscription_tier' => 'premium',
            'premium_ends_at' => $endDate,
        ]);

        \Log::info('Subscription activated manually:', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'plan' => $subscription->plan,
            'end_date' => $endDate
        ]);

        return $this->redirectToDashboard('success', 'Payment successful! Premium subscription activated.');
    }

    /**
     * Create new subscription
     */
    private function createNewSubscription($orderId)
    {
        \Log::info('Creating new subscription for order:', ['order_id' => $orderId]);

        $user = auth()->user();
        
        // Get plan details from order_id or use default
        $planDetails = $this->extractPlanFromOrderId($orderId);
        
        // Create active subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'provider' => 'midtrans',
            'subscription_id' => $orderId,
            'status' => 'active',
            'plan' => $planDetails['plan'],
            'amount_idr' => $planDetails['amount'],
            'start_date' => now(),
            'end_date' => $planDetails['end_date'],
        ]);

        // Activate user premium
        $user->update([
            'subscription_tier' => 'premium',
            'premium_ends_at' => $subscription->end_date,
        ]);

        \Log::info('New subscription created and activated:', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'order_id' => $orderId,
            'plan' => $planDetails['plan']
        ]);

        return $this->redirectToDashboard('success', 'Payment successful! Premium subscription activated.');
    }

    /**
     * Extract plan information from order ID
     */
    private function extractPlanFromOrderId($orderId)
    {
        // Extract plan information from order_id
        // Format order_id: TRADING-{USER_ID}-{PLAN}-{TIMESTAMP}
        $parts = explode('-', $orderId);
        $plan = $parts[2] ?? 'monthly';
        
        $planDetails = [
            'monthly' => [
                'amount' => 299000,
                'end_date' => now()->addMonth()
            ],
            '6months' => [
                'amount' => 1497000,
                'end_date' => now()->addMonths(6)
            ],
            'yearly' => [
                'amount' => 2508000,
                'end_date' => now()->addYear()
            ]
        ];

        return [
            'plan' => $plan,
            'amount' => $planDetails[$plan]['amount'] ?? 299000,
            'end_date' => $planDetails[$plan]['end_date'] ?? now()->addMonth()
        ];
    }

    /**
     * Get plan end date
     */
    private function getPlanEndDate($plan)
    {
        $durations = [
            'monthly' => now()->addMonth(),
            '6months' => now()->addMonths(6),
            'yearly' => now()->addYear()
        ];

        return $durations[$plan] ?? now()->addMonth();
    }

    /**
     * Handle failed payment
     */
    private function handleFailedPayment($status, $orderId)
    {
        \Log::warning('Payment failed or cancelled', [
            'status' => $status,
            'order_id' => $orderId,
            'user_id' => auth()->id()
        ]);

        $errorMessage = 'Payment failed or was cancelled.';

        if ($status) {
            $errorMessage .= ' Status: ' . $status;
        }

        // Update subscription status to failed if exists
        if ($orderId) {
            $subscription = Subscription::where('subscription_id', $orderId)->first();
            if ($subscription) {
                $subscription->update(['status' => 'failed']);
                \Log::info('Subscription marked as failed:', ['order_id' => $orderId]);
            }
        }

        return redirect()->route('subscription')
            ->with('error', $errorMessage);
    }

    /**
     * Payment cancel page
     */
    public function paymentCancel()
    {
        \Log::info('Payment cancelled by user', ['user_id' => auth()->id()]);
        
        return redirect()->route('subscription')
            ->with('info', 'Payment was cancelled. You can try again anytime.');
    }

    /**
     * Redirect to dashboard with message
     */
    private function redirectToDashboard($type, $message)
    {
        \Log::info('Redirecting to dashboard', ['type' => $type, 'message' => $message]);
        return redirect()->route('dashboard')->with($type, $message);
    }

    /**
     * Handle Midtrans webhook
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        
        \Log::info('=== MIDTRANS WEBHOOK RECEIVED ===', $payload);

        try {
            $success = $this->midtransService->handleWebhook($payload);
            
            if ($success) {
                \Log::info('Webhook processed successfully');
                return response()->json(['status' => 'success']);
            } else {
                \Log::info('Webhook ignored - no action taken');
                return response()->json(['status' => 'ignored'], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Webhook processing error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string'
        ]);

        $orderId = $request->order_id;

        \Log::info('Checking payment status', ['order_id' => $orderId]);

        try {
            $status = $this->midtransService->checkPaymentStatus($orderId);
            
            \Log::info('Payment status result:', [
                'order_id' => $orderId,
                'success' => $status['success'],
                'transaction_status' => $status['transaction_status'] ?? null
            ]);

            return response()->json($status);
        } catch (\Exception $e) {
            \Log::error('Payment status check failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's subscription history
     */
    public function subscriptionHistory()
    {
        $user = auth()->user();
        $subscriptions = Subscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions
        ]);
    }

    /**
     * Validate Midtrans configuration
     */
    public function validateConfiguration()
    {
        try {
            $result = $this->midtransService->validateConfiguration();
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Configuration validation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Configuration validation failed: ' . $e->getMessage()
            ], 500);
        }
    }
}