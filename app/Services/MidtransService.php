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
            // Use appropriate payment method based on type
            if (in_array($paymentMethod, ['qris', 'va'])) {
                return $this->createCoreApiPayment($user, $plan, $paymentMethod, $priceIdr, $orderId, $transactionDetails, $customerDetails);
            } elseif ($paymentMethod === 'credit_card') {
                return $this->createCreditCardPayment($user, $plan, $paymentMethod, $priceIdr, $orderId, $transactionDetails, $customerDetails);
            } else {
                return $this->createSnapPayment($user, $plan, $paymentMethod, $priceIdr, $orderId, $transactionDetails, $customerDetails);
            }

        } catch (\Exception $e) {
            \Log::error('Payment creation failed: ' . $e->getMessage());
            throw new \Exception('Payment initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Create Core API payment for QRIS and VA
     */
    private function createCoreApiPayment($user, $plan, $paymentMethod, $priceIdr, $orderId, $transactionDetails, $customerDetails)
    {
        $mappedMethod = $this->mapPaymentMethod($paymentMethod);
        
        $transactionData = [
            'payment_type' => $mappedMethod,
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
        ];

        // Add payment-specific parameters
        if ($paymentMethod === 'qris') {
            $transactionData['qris'] = ['acquirer' => 'gopay'];
        } elseif ($paymentMethod === 'va') {
            $transactionData['bank_transfer'] = ['bank' => 'bca'];
        }

        \Log::info('Core API transaction data:', $transactionData);

        try {
            $charge = CoreApi::charge($transactionData);
            
            \Log::info('Core API charge response:', [
                'status' => $charge->status ?? null,
                'payment_type' => $charge->payment_type ?? null,
                'order_id' => $charge->order_id ?? null
            ]);

            // Create pending subscription
            $this->createPendingSubscription($user, $orderId, $plan, $priceIdr);

            return [
                'order_id' => $orderId,
                'payment_data' => $charge,
                'payment_method' => $paymentMethod,
                'amount' => $priceIdr,
                'plan' => $plan,
                'payment_instructions' => $this->generateCoreApiInstructions($paymentMethod, $charge, $priceIdr)
            ];
            
        } catch (\Exception $e) {
            \Log::error('Core API payment failed: ' . $e->getMessage());
            throw new \Exception($paymentMethod . ' payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Create Credit Card payment
     */
    private function createCreditCardPayment($user, $plan, $paymentMethod, $priceIdr, $orderId, $transactionDetails, $customerDetails)
    {
        \Log::info('Creating Credit Card payment...');

        $transactionData = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'credit_card' => [
                'secure' => true,
                'bank' => 'bni',
            ],
            'callbacks' => [
                'finish' => url('/payment/finish')
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($transactionData);
            
            \Log::info('Credit Card Snap token generated:', [
                'order_id' => $orderId,
                'has_token' => !empty($snapToken)
            ]);

            // Create pending subscription
            $this->createPendingSubscription($user, $orderId, $plan, $priceIdr);

            return [
                'order_id' => $orderId,
                'snap_token' => $snapToken,
                'payment_method' => $paymentMethod,
                'amount' => $priceIdr,
                'plan' => $plan,
                'payment_instructions' => $this->generateCreditCardInstructions($snapToken, $priceIdr)
            ];
            
        } catch (\Exception $e) {
            \Log::error('Credit Card payment failed: ' . $e->getMessage());
            throw new \Exception('Credit Card payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Create Snap payment for other methods
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

        try {
            $snapToken = Snap::getSnapToken($transactionData);
            
            // Create pending subscription
            $this->createPendingSubscription($user, $orderId, $plan, $priceIdr);

            return [
                'order_id' => $orderId,
                'snap_token' => $snapToken,
                'payment_method' => $paymentMethod,
                'amount' => $priceIdr,
                'plan' => $plan,
                'payment_instructions' => $this->generateSnapInstructions($paymentMethod, $snapToken, $priceIdr)
            ];
            
        } catch (\Exception $e) {
            \Log::error('Snap payment failed: ' . $e->getMessage());
            throw new \Exception('Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate instructions for Credit Card
     */
    private function generateCreditCardInstructions($snapToken, $amount)
    {
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');
        
        return [
            'type' => 'credit_card',
            'snap_token' => $snapToken,
            'instructions' => [
                'Click the "Complete Payment" button below',
                'You will be redirected to secure payment page',
                'Enter your credit card details',
                '3D Secure authentication will be required',
                'Amount: ' . $formattedAmount
            ]
        ];
    }

    /**
     * Generate payment instructions for Core API responses
     */
    private function generateCoreApiInstructions($paymentMethod, $chargeData, $amount)
    {
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');
        
        switch ($paymentMethod) {
            case 'qris':
                $qrisContent = $chargeData->qr_string ?? null;
                $qrCodeUrl = $qrisContent ? 
                    'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrisContent) :
                    null;
                    
                return [
                    'type' => 'qris',
                    'qris_content' => $qrisContent,
                    'qr_code_url' => $qrCodeUrl,
                    'instructions' => [
                        'Scan QR code using your mobile banking app',
                        'Confirm payment in your banking app',
                        'Amount: ' . $formattedAmount,
                        'Payment will be verified automatically'
                    ]
                ];
                
            case 'va':
                $vaNumber = $chargeData->va_numbers[0]->va_number ?? null;
                $bank = $chargeData->va_numbers[0]->bank ?? 'BCA';
                
                return [
                    'type' => 'va',
                    'va_number' => $vaNumber,
                    'bank' => strtoupper($bank),
                    'instructions' => [
                        'Transfer the exact amount: ' . $formattedAmount,
                        'Use Virtual Account Number: ' . ($vaNumber ?? 'Processing...'),
                        'Bank: ' . strtoupper($bank),
                        'Payment will be verified automatically'
                    ]
                ];
                
            default:
                return [
                    'type' => 'unknown',
                    'instructions' => ['Please complete the payment process'],
                ];
        }
    }

    /**
     * Generate payment instructions for Snap responses
     */
    private function generateSnapInstructions($paymentMethod, $snapToken, $amount)
    {
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');
        
        return [
            'type' => 'snap_redirect',
            'snap_token' => $snapToken,
            'instructions' => [
                'You will be redirected to payment page',
                'Complete your payment there',
                'Amount: ' . $formattedAmount
            ]
        ];
    }

    /**
     * Map payment method to Midtrans payment type
     */
    private function mapPaymentMethod($paymentMethod)
    {
        $mapping = [
            'qris' => 'qris',
            'va' => 'bank_transfer',
            'credit_card' => 'credit_card'
        ];
        
        return $mapping[$paymentMethod] ?? $paymentMethod;
    }

    /**
     * Get plan prices
     */
    private function getPlanPrices()
    {
        return [
            'monthly' => 299000,
            '6months' => 1497000, 
            'yearly' => 2508000
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
     * Alias method for embedded payment
     */
    public function createEmbeddedPayment(User $user, string $plan, string $paymentMethod)
    {
        return $this->createSubscription($user, $plan, $paymentMethod);
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
                'message' => 'Midtrans configuration is valid'
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