<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Plan;
use Razorpay\Api\Api;
use Exception;

class RazorpayService
{
    protected $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    /**
     * Create a Razorpay customer
     */
    public function createCustomer($user)
    {
        try {
            $customer = $this->api->customer->create([
                'email' => $user->email,
                'contact' => $user->phone ?? '',
                'name' => $user->name,
            ]);

            return $customer;
        } catch (Exception $e) {
            throw new Exception('Failed to create Razorpay customer: ' . $e->getMessage());
        }
    }

    /**
     * Create a Razorpay subscription
     */
    public function createSubscription($user, Plan $plan, $customerId = null)
    {
        try {
            if (!$customerId) {
                $customer = $this->createCustomer($user);
                $customerId = $customer->id;
            }

            $subscriptionData = [
                'plan_id' => config('services.razorpay.plans.' . strtolower($plan->duration)),
                'customer_notify' => 1,
                'quantity' => 1,
                'total_count' => $plan->duration === 'lifetime' ? 1 : 0, // 0 for infinite
                'addons' => [
                    [
                        'item' => [
                            'name' => $plan->name,
                            'description' => $plan->description,
                            'amount' => (int)($plan->price * 100), // Convert to paise
                        ]
                    ]
                ],
            ];

            $subscription = $this->api->subscription->create($subscriptionData);

            return [
                'subscription' => $subscription,
                'customer_id' => $customerId,
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to create Razorpay subscription: ' . $e->getMessage());
        }
    }

    /**
     * Create a Razorpay payment
     */
    public function createPayment($amount, $customerId, $description)
    {
        try {
            $payment = $this->api->payment->create([
                'amount' => (int)($amount * 100), // Convert to paise
                'currency' => 'INR',
                'customer_id' => $customerId,
                'description' => $description,
                'receipt' => 'receipt#' . time(),
            ]);

            return $payment;
        } catch (Exception $e) {
            throw new Exception('Failed to create Razorpay payment: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment signature
     */
    public function verifyPaymentSignature($paymentId, $signature)
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);

            if ($payment->status === 'captured') {
                return [
                    'success' => true,
                    'payment' => $payment,
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment not captured',
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to verify payment: ' . $e->getMessage());
        }
    }

    /**
     * Verify subscription signature (for webhook)
     */
    public function verifySubscriptionWebhook($data, $signature)
    {
        try {
            $expectedSignature = hash_hmac(
                'sha256',
                json_encode($data),
                config('services.razorpay.secret')
            );

            if (!hash_equals($expectedSignature, $signature)) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to verify webhook: ' . $e->getMessage());
        }
    }

    /**
     * Cancel Razorpay subscription
     */
    public function cancelSubscription($subscriptionId)
    {
        try {
            $subscription = $this->api->subscription->fetch($subscriptionId);
            $subscription->cancel();

            return $subscription;
        } catch (Exception $e) {
            throw new Exception('Failed to cancel Razorpay subscription: ' . $e->getMessage());
        }
    }

    /**
     * Pause Razorpay subscription
     */
    public function pauseSubscription($subscriptionId, $pauseAt = null)
    {
        try {
            $subscription = $this->api->subscription->fetch($subscriptionId);
            $subscription->pause(['pauseAt' => $pauseAt]);

            return $subscription;
        } catch (Exception $e) {
            throw new Exception('Failed to pause Razorpay subscription: ' . $e->getMessage());
        }
    }

    /**
     * Resume Razorpay subscription
     */
    public function resumeSubscription($subscriptionId)
    {
        try {
            $subscription = $this->api->subscription->fetch($subscriptionId);
            $subscription->resume();

            return $subscription;
        } catch (Exception $e) {
            throw new Exception('Failed to resume Razorpay subscription: ' . $e->getMessage());
        }
    }

    /**
     * Fetch Razorpay subscription
     */
    public function fetchSubscription($subscriptionId)
    {
        try {
            return $this->api->subscription->fetch($subscriptionId);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch Razorpay subscription: ' . $e->getMessage());
        }
    }

    /**
     * Create Razorpay invoice
     */
    public function createInvoice($subscriptionId, $description)
    {
        try {
            $invoice = $this->api->invoice->create([
                'subscription_id' => $subscriptionId,
                'description' => $description,
            ]);

            return $invoice;
        } catch (Exception $e) {
            throw new Exception('Failed to create invoice: ' . $e->getMessage());
        }
    }
}
