<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\User;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Exception;

class SubscriptionController extends Controller
{
    protected $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    /**
     * Get user's active subscription
     */
    public function current(Request $request)
    {
        try {
            $user = auth()->user();
            $subscription = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->with('plan')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Current subscription retrieved',
                'data' => new SubscriptionResource($subscription),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving subscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all subscriptions for user
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $status = $request->query('status');
            $limit = $request->query('limit', 15);

            $query = Subscription::where('user_id', $user->id)->with('plan');

            if ($status) {
                $query->where('status', $status);
            }

            $subscriptions = $query->latest()->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => 'Subscriptions retrieved successfully',
                'data' => SubscriptionResource::collection($subscriptions),
                'pagination' => [
                    'total' => $subscriptions->total(),
                    'count' => $subscriptions->count(),
                    'per_page' => $subscriptions->perPage(),
                    'current_page' => $subscriptions->currentPage(),
                    'last_page' => $subscriptions->lastPage(),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving subscriptions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get subscription by ID
     */
    public function show($id)
    {
        try {
            $user = auth()->user();
            $subscription = Subscription::where('id', $id)
                ->where('user_id', $user->id)
                ->with('plan')
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Subscription retrieved successfully',
                'data' => new SubscriptionResource($subscription),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create new subscription (Initiate payment)
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $validated = $request->validate([
                'plan_id' => 'required|exists:plans,id',
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);

            // Check if user already has active subscription
            $existingSubscription = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($existingSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has an active subscription',
                    'data' => new SubscriptionResource($existingSubscription),
                ], 409);
            }

            // Create pending subscription
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'amount' => $plan->price,
                'tax_amount' => 0,
                'total_amount' => $plan->price,
                'billing_cycle' => $plan->duration,
                'billing_cycle_days' => $plan->billing_cycle_days,
            ]);

            // Create Razorpay order for payment
            try {
                $order = $this->createRazorpayOrder($user, $plan, $subscription);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription initiated. Complete payment to activate.',
                    'data' => [
                        'subscription' => new SubscriptionResource($subscription),
                        'razorpay_order' => $order,
                    ],
                ], 201);
            } catch (Exception $e) {
                $subscription->delete();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating subscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify payment and activate subscription
     */
    public function verifyPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'subscription_id' => 'required|exists:subscriptions,id',
                'razorpay_payment_id' => 'required',
                'razorpay_signature' => 'required',
            ]);

            $user = auth()->user();
            $subscription = Subscription::where('id', $validated['subscription_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Verify payment with Razorpay
            $paymentVerified = $this->razorpayService->verifyPaymentSignature(
                $validated['razorpay_payment_id'],
                $validated['razorpay_signature']
            );

            if (!$paymentVerified['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed',
                ], 400);
            }

            // Update subscription
            $subscription->update([
                'payment_status' => 'completed',
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'status' => 'active',
            ]);

            // Calculate dates
            $subscription->calculateRenewalDates();
            $subscription->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified. Subscription activated!',
                'data' => new SubscriptionResource($subscription),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verifying payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $subscription = Subscription::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $reason = $request->input('reason', 'User requested cancellation');

            // Cancel with Razorpay if subscription ID exists
            if ($subscription->razorpay_subscription_id) {
                $this->razorpayService->cancelSubscription($subscription->razorpay_subscription_id);
            }

            $subscription->cancel($reason);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
                'data' => new SubscriptionResource($subscription),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling subscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pause subscription
     */
    public function pause(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $subscription = Subscription::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($subscription->razorpay_subscription_id) {
                $this->razorpayService->pauseSubscription($subscription->razorpay_subscription_id);
            }

            $subscription->pause();

            return response()->json([
                'success' => true,
                'message' => 'Subscription paused successfully',
                'data' => new SubscriptionResource($subscription),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error pausing subscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resume subscription
     */
    public function resume(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $subscription = Subscription::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($subscription->razorpay_subscription_id) {
                $this->razorpayService->resumeSubscription($subscription->razorpay_subscription_id);
            }

            $subscription->resume();

            return response()->json([
                'success' => true,
                'message' => 'Subscription resumed successfully',
                'data' => new SubscriptionResource($subscription),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resuming subscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get subscription stats
     */
    public function stats()
    {
        try {
            $user = auth()->user();
            
            $totalSubscriptions = Subscription::where('user_id', $user->id)->count();
            $activeSubscriptions = Subscription::where('user_id', $user->id)->active()->count();
            $expiredSubscriptions = Subscription::where('user_id', $user->id)->expired()->count();
            $cancelledSubscriptions = Subscription::where('user_id', $user->id)->where('status', 'cancelled')->count();
            $expiringSubscriptions = Subscription::where('user_id', $user->id)->expiring()->count();

            return response()->json([
                'success' => true,
                'message' => 'Subscription stats retrieved',
                'data' => [
                    'total_subscriptions' => $totalSubscriptions,
                    'active_subscriptions' => $activeSubscriptions,
                    'expired_subscriptions' => $expiredSubscriptions,
                    'cancelled_subscriptions' => $cancelledSubscriptions,
                    'expiring_soon' => $expiringSubscriptions,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Razorpay order
     */
    private function createRazorpayOrder($user, Plan $plan, Subscription $subscription)
    {
        try {
            $api = new \Razorpay\Api\Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            $order = $api->order->create([
                'amount' => (int)($plan->price * 100), // Amount in paise
                'currency' => 'INR',
                'receipt' => 'subscription_' . $subscription->id,
                'notes' => [
                    'subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                    'user_id' => $user->id,
                    'plan_name' => $plan->name,
                ],
            ]);

            return $order;
        } catch (Exception $e) {
            throw new Exception('Failed to create Razorpay order: ' . $e->getMessage());
        }
    }
}
