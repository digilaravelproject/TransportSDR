<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManageSubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = [
            [
                'id' => 1,
                'tenant_name' => 'Shiv Travels Lucknow',
                'plan_name' => 'Professional Plan',
                'email' => 'admin@shivtravels.com',
                'status' => 'Active',
                'start_date' => '2026-04-01',
                'end_date' => '2026-05-01',
                'amount' => 699,
                'renewal_date' => '2026-05-01',
            ],
            [
                'id' => 2,
                'tenant_name' => 'Raj Express Transport',
                'plan_name' => 'Basic Plan',
                'email' => 'admin@rajexpress.com',
                'status' => 'Active',
                'start_date' => '2026-03-15',
                'end_date' => '2026-04-15',
                'amount' => 299,
                'renewal_date' => '2026-04-15',
            ],
            [
                'id' => 3,
                'tenant_name' => 'Premium Logistics Ltd',
                'plan_name' => 'Enterprise Plan',
                'email' => 'admin@premiumlogistics.com',
                'status' => 'Active',
                'start_date' => '2026-02-20',
                'end_date' => '2026-05-20',
                'amount' => 1499,
                'renewal_date' => '2026-05-20',
            ],
            [
                'id' => 4,
                'tenant_name' => 'City Transport Co',
                'plan_name' => 'Starter Plan',
                'email' => 'admin@citytransport.com',
                'status' => 'Expired',
                'start_date' => '2026-03-01',
                'end_date' => '2026-04-01',
                'amount' => 99,
                'renewal_date' => '2026-04-01',
            ],
            [
                'id' => 5,
                'tenant_name' => 'Quick Travel Services',
                'plan_name' => 'Professional Plan',
                'email' => 'admin@quicktravel.com',
                'status' => 'Active',
                'start_date' => '2026-04-05',
                'end_date' => '2026-05-05',
                'amount' => 699,
                'renewal_date' => '2026-05-05',
            ],
        ];

        return view('admin.subscriptions.index', compact('subscriptions'));
    }
}
