<?php
namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\CoreApi;
use Midtrans\Transaction;
use Carbon\Carbon;

class MidtransService
{
    public function __construct()
    {
        $this->initializeMidtrans();
    }

    /**
     * Initialize Midtrans configuration
     */
    private function initializeMidtrans()
    {
        MidtransConfig::$serverKey = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = config('services.midtrans.is_production');
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
        
        \Log::info('Midtrans Config Initialized:', [
            'is_production' => config('services.midtrans.is_production'),
            'server_key_exists' => !empty(config('services.midtrans.server_key')),
            'client_key' => config('services.midtrans.client_key')
        ]);
    }

    /**
     * Create payment for subscription - Main method
     */
    public function createSubscription(User $user, string $plan, string $paymentMethod)
    {
        $prices = $this->getPlanPrices();
        $priceIdr = $prices[$plan];
        $orderId = 'TRADING-' . $user->id . '-' . $plan . '-' . time();
        
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $priceIdr,
        ];

        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '08123456789',
        ];

        \Log::info('Creating subscription payment:', [
            'user_id' => $user->id,
            'plan' => $plan,
            'payment_method' => $paymentMethod,
            'amount' => $priceIdr,
            'order_id' => $orderId
        ]);

        try {
            // ✅ GUNAKAN SNAP API UNTUK SEMUA PAYMENT METHOD
            // "QRIS Dinamis GoPay" hanya tersedia di Snap API, bukan Core API
            return $this->createSnapPayment($user, $plan, $paymentMethod, $priceIdr, $orderId, $transactionDetails, $customerDetails);

        } catch (\Exception $e) {
            \Log::error('Payment creation failed: ' . $e->getMessage());
            throw new \Exception('Payment initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Create Snap payment for ALL methods (QRIS, VA, Credit Card)
     */
    private function createSnapPayment($user, $plan, $paymentMethod, $priceIdr, $orderId, $transactionDetails, $customerDetails)
    {
        $transactionData = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'callbacks' => [
                'finish' => url('/payment/finish')
            ]
        ];

        // ✅ TAMBAHKAN ENABLED PAYMENTS UNTUK MEMAKSA QRIS
        $enabledPayments = $this->getEnabledPayments($paymentMethod);
        if (!empty($enabledPayments)) {
            $transactionData['enabled_payments'] = $enabledPayments;
        }

        \Log::info('Snap transaction data:', [
            'order_id' => $orderId,
            'payment_method' => $paymentMethod,
            'enabled_payments' => $enabledPayments,
            'amount' => $priceIdr
        ]);

        try {
            $snapToken = Snap::getSnapToken($transactionData);
            
            \Log::info('Snap token generated successfully:', [
                'order_id' => $orderId,
                'has_token' => !empty($snapToken),
                'payment_method' => $paymentMethod
            ]);

            // Create pending subscription
            $this->createPendingSubscription($user, $orderId, $plan, $priceIdr);

            // ✅ STANDARDIZED RESPONSE
            return [
                'order_id' => $orderId,
                'snap_token' => $snapToken,
                'payment_method' => $paymentMethod,
                'amount' => $priceIdr,
                'plan' => $plan,
                'redirect_url' => config('services.midtrans.is_production') 
                    ? "https://app.midtrans.com/snap/v2/vtweb/{$snapToken}"
                    : "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}",
                'type' => 'snap' // ✅ ADD TYPE FOR FRONTEND
            ];
            
        } catch (\Exception $e) {
            \Log::error('Snap payment failed: ' . $e->getMessage());
            \Log::error('Snap error details:', [
                'order_id' => $orderId,
                'payment_method' => $paymentMethod,
                'error_trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Get enabled payments based on selected method
     */
    private function getEnabledPayments($paymentMethod)
    {
        // ✅ MAP PAYMENT METHOD KE SNAP PAYMENT TYPES
        $mapping = [
            'qris' => ['gopay', 'shopeepay'], // QRIS via GoPay/ShopeePay
            'va' => ['bni_va', 'bri_va', 'bca_va', 'permata_va'],
            'credit_card' => ['credit_card']
        ];

        return $mapping[$paymentMethod] ?? [];
    }

    /**
     * Create Core API payment (HAPUS/COMMENT - tidak digunakan)
     */
    /*
    private function createCoreApiPayment($user, $plan, $paymentMethod, $priceIdr, $orderId, $transactionDetails, $customerDetails)
    {
        // ❌ JANGAN GUNAKAN CORE API UNTUK QRIS DINAMIS GOPAY
        // Hanya tersedia di Snap API
    }
    */

    /**
     * Create Credit Card payment (HAPUS/COMMENT - sudah digabung ke Snap)
     */
    /*
    private function createCreditCardPayment($user, $plan, $paymentMethod, $priceIdr, $orderId, $transactionDetails, $customerDetails)
    {
        // ❌ SUDAH DIGABUNG KE createSnapPayment
    }
    */

    /**
     * Get plan prices
     */
    private function getPlanPrices()
    {
        return [
            'monthly' => 490000,      // 490 ribu
            '6months' => 2500000,     // 2.5 juta  
            'yearly' => 4850000       // 4.85 juta
        ];
    }

    /**
     * Create pending subscription record
     */
    private function createPendingSubscription($user, $orderId, $plan, $amount)
    {
        Subscription::create([
            'user_id' => $user->id,
            'provider' => 'midtrans',
            'subscription_id' => $orderId,
            'status' => 'pending',
            'plan' => $plan,
            'amount_idr' => $amount,
            'start_date' => now(),
            'end_date' => $this->getPlanEndDate($plan),
        ]);

        \Log::info('Pending subscription created:', [
            'user_id' => $user->id,
            'order_id' => $orderId,
            'plan' => $plan
        ]);
    }

    /**
     * Get plan end date
     */
    private function getPlanEndDate(string $plan): Carbon
    {
        return match($plan) {
            'monthly' => now()->addMonth(),
            '6months' => now()->addMonths(6),
            'yearly' => now()->addYear(),
            default => now()->addMonth(),
        };
    }

    /**
     * Handle webhook notifications
     */
    public function handleWebhook($payload)
    {
        \Log::info('=== MIDTRANS WEBHOOK RECEIVED ===', $payload);

        $orderId = $payload['order_id'];
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        \Log::info('Webhook processing:', [
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'fraud_status' => $fraudStatus
        ]);

        // Find subscription
        $subscription = Subscription::where('subscription_id', $orderId)->first();

        if (!$subscription) {
            \Log::error('Subscription not found for webhook:', ['order_id' => $orderId]);
            return false;
        }

        // Handle payment status
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                return $this->activateSubscription($subscription);
            } else {
                $subscription->update(['status' => 'failed']);
                \Log::warning('Fraud detected for order:', ['order_id' => $orderId]);
            }
        } else if ($transactionStatus === 'settlement') {
            return $this->activateSubscription($subscription);
        } else if ($transactionStatus === 'pending') {
            \Log::info('Payment pending for order:', ['order_id' => $orderId]);
            $subscription->update(['status' => 'pending']);
        } else if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $subscription->update(['status' => 'failed']);
            \Log::info('Payment failed for order:', ['order_id' => $orderId]);
        }

        return false;
    }

    /**
     * Activate subscription
     */
    private function activateSubscription($subscription)
    {
        \Log::info('Activating subscription via webhook:', ['subscription_id' => $subscription->id]);

        $subscription->update([
            'status' => 'active',
            'start_date' => now(),
            'end_date' => $this->getPlanEndDate($subscription->plan),
        ]);

        $user = $subscription->user;
        $user->update([
            'subscription_tier' => 'premium',
            'premium_ends_at' => $subscription->end_date,
        ]);

        \Log::info('Premium activated via webhook:', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'plan' => $subscription->plan
        ]);

        return true;
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus($orderId)
    {
        try {
            $status = Transaction::status($orderId);
            
            \Log::info('Payment status checked:', [
                'order_id' => $orderId,
                'status_code' => $status->status_code,
                'transaction_status' => $status->transaction_status
            ]);
            
            return [
                'success' => true,
                'status' => $status->status_code,
                'transaction_status' => $status->transaction_status,
                'fraud_status' => $status->fraud_status ?? null,
                'payment_type' => $status->payment_type ?? null
            ];
        } catch (\Exception $e) {
            \Log::error('Payment status check failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to check payment status'
            ];
        }
    }

    /**
     * Validate Midtrans configuration
     */
    public function validateConfiguration()
    {
        try {
            // Test configuration by creating a small transaction
            $testData = [
                'transaction_details' => [
                    'order_id' => 'TEST-' . time(),
                    'gross_amount' => 1000,
                ],
            ];
            
            $snapToken = Snap::getSnapToken($testData);
            
            return [
                'success' => true,
                'message' => 'Midtrans configuration is valid',
                'snap_token' => $snapToken
            ];
        } catch (\Exception $e) {
            \Log::error('Midtrans configuration validation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Midtrans configuration error: ' . $e->getMessage()
            ];
        }
    }
}