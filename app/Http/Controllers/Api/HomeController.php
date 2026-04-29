<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Trip, CashBookEntry, Lead, Vehicle, Notification};
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function stats(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $today = now()->toDateString();

        $todayTrips = Trip::where('tenant_id', $tenantId)->whereDate('trip_date', $today)->count();

        $dailyRevenue = CashBookEntry::where('tenant_id', $tenantId)
            ->where('entry_type', 'income')
            ->whereDate('entry_date', $today)
            ->sum('amount');

        $pendingLeads = Lead::where('tenant_id', $tenantId)->where('status', 'pending')->count();

        $activeVehicles = Vehicle::where('tenant_id', $tenantId)->where('is_active', true)->count();

        // recent activity — use notifications as activity feed
        $recent = Notification::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->limit(8)->get(['id','type','title','message','data','created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'today_trips' => (int) $todayTrips,
                'daily_revenue' => (float) $dailyRevenue,
                'pending_leads' => (int) $pendingLeads,
                'active_vehicles' => (int) $activeVehicles,
                'recent_activity' => $recent,
            ]
        ]);
    }
}
