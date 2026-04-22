<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;

class ManageSubscriptionsController extends Controller
{
    /**
     * Display all subscriptions
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $paymentStatus = $request->query('payment_status');
        $search = $request->query('search');
        
        $query = Subscription::with(['user', 'plan']);
        
        if ($status && in_array($status, ['active', 'pending', 'cancelled', 'expired', 'paused'])) {
            $query->where('status', $status);
        }
        
        if ($paymentStatus && in_array($paymentStatus, ['pending', 'completed', 'failed', 'refunded'])) {
            $query->where('payment_status', $paymentStatus);
        }
        
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $subscriptions = $query->latest()->paginate(15);
        
        // Get statistics
        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'pending' => Subscription::where('status', 'pending')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
            'total_revenue' => Subscription::where('payment_status', 'completed')->sum('total_amount'),
        ];
        
        return view('admin.subscriptions.index', compact('subscriptions', 'status', 'paymentStatus', 'search', 'stats'));
    }

    /**
     * Show subscription details
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'plan']);
        return view('admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Edit subscription
     */
    public function edit(Subscription $subscription)
    {
        $subscription->load(['user', 'plan']);
        $plans = Plan::active()->get();
        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    /**
     * Update subscription
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,pending,cancelled,expired,paused',
            'payment_status' => 'required|in:pending,completed,failed,refunded',
            'plan_id' => 'required|exists:plans,id',
            'notes' => 'nullable|string',
        ]);

        $subscription->update($validated);

        return redirect()->route('admin.subscriptions.show', $subscription->id)
                        ->with('success', 'Subscription updated successfully!');
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $subscription->cancel($validated['reason'] ?? 'Cancelled by admin');

        return redirect()->route('admin.subscriptions.show', $subscription->id)
                        ->with('success', 'Subscription cancelled successfully!');
    }

    /**
     * Renew subscription
     */
    public function renew(Subscription $subscription)
    {
        $subscription->renew();

        return redirect()->route('admin.subscriptions.show', $subscription->id)
                        ->with('success', 'Subscription renewed successfully!');
    }

    /**
     * Get statistics
     */
    public function statistics()
    {
        $monthlyRevenue = Subscription::where('payment_status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->sum('total_amount');

        $subscriptionsByPlan = Subscription::with('plan')
            ->where('status', 'active')
            ->selectRaw('plan_id, COUNT(*) as total')
            ->groupBy('plan_id')
            ->get();

        $subscriptionsByStatus = Subscription::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $expiringSubscriptions = Subscription::expiring()->count();
        $expiredSubscriptions = Subscription::expired()->count();

        return view('admin.subscriptions.statistics', compact(
            'monthlyRevenue',
            'subscriptionsByPlan',
            'subscriptionsByStatus',
            'expiringSubscriptions',
            'expiredSubscriptions'
        ));
    }

    /**
     * Export subscriptions
     */
    public function export()
    {
        $subscriptions = Subscription::with(['user', 'plan'])->get();
        
        $filename = 'subscriptions_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        $columns = ['ID', 'User Name', 'User Email', 'Plan', 'Status', 'Payment Status', 'Amount', 'Start Date', 'End Date', 'Created At'];

        $callback = function () use ($subscriptions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($subscriptions as $subscription) {
                fputcsv($file, [
                    $subscription->id,
                    $subscription->user->name,
                    $subscription->user->email,
                    $subscription->plan->name,
                    $subscription->status,
                    $subscription->payment_status,
                    $subscription->total_amount,
                    $subscription->start_date?->format('d-m-Y'),
                    $subscription->end_date?->format('d-m-Y'),
                    $subscription->created_at->format('d-m-Y H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
