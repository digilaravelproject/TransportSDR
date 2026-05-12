<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\OnlinePayment;
use App\Models\Inventory;
use App\Models\VehicleMaintenanceLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = now()->format('Y');

        // Trip Metrics
        $totalPieces = Trip::count();
        $delivered = Trip::whereIn('status', ['delivered','completed','paid'])->count();
        $activeTracking = Trip::whereNotIn('status', ['delivered','completed'])->count();

        // Vehicle Metrics
        $totalVehicles = Vehicle::count();
        $activeVehicles = Vehicle::where('is_active', true)->count();
        $vehicleInMaintenance = VehicleMaintenanceLog::where('status', '!=', 'completed')->count();

        // Shift Metrics
        $totalShifts = Shift::count();
        $activeShifts = Shift::where('is_active', true)->count();

        // Staff Metrics
        $totalStaff = Staff::count();
        $activeStaff = Staff::where('is_active', true)->count();
        $staffOnLeave = Staff::where('is_active', true)->where('is_available', false)->count();

        // Lead Metrics
        $totalLeads = Lead::count();
        $qualifiedLeads = Lead::where('status', 'qualified')->count();
        $convertedLeads = Lead::where('status', 'converted')->count();

        // Customer Metrics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();

        // Payment Metrics
        $totalPayments = OnlinePayment::count();
        $completedPayments = OnlinePayment::where('status', 'completed')->count();
        $pendingPayments = OnlinePayment::where('status', 'pending')->count();

        // Inventory Metrics
        $totalInventoryItems = Inventory::count();
        $lowStockItems = Inventory::where('quantity_in_stock', '<', 10)->count();

        // Monthly trip counts
        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[] = Trip::whereYear('created_at', $year)->whereMonth('created_at', $m)->count();
        }

        $monthlyJson = json_encode($monthly);

        $recent = Trip::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalPieces','delivered','activeTracking',
            'totalVehicles','activeVehicles','vehicleInMaintenance',
            'totalShifts','activeShifts',
            'totalStaff','activeStaff','staffOnLeave',
            'totalLeads','qualifiedLeads','convertedLeads',
            'totalCustomers','activeCustomers',
            'totalPayments','completedPayments','pendingPayments',
            'totalInventoryItems','lowStockItems',
            'monthly','monthlyJson','recent'
        ));
    }
}
