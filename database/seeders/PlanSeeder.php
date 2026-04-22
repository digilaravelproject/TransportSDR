<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter Plan',
                'description' => 'Perfect for small transport businesses just getting started',
                'price' => 99.00,
                'duration' => 'monthly',
                'billing_cycle_days' => 30,
                'max_vehicles' => 2,
                'max_trips_per_month' => 20,
                'max_staff' => 5,
                'features' => ['Email Support', 'Basic Tracking', 'Mobile App'],
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro Agency',
                'description' => 'Comprehensive solution for growing transport agencies',
                'price' => 299.00,
                'duration' => 'monthly',
                'billing_cycle_days' => 30,
                'max_vehicles' => 20,
                'max_trips_per_month' => 200,
                'max_staff' => 50,
                'features' => ['Priority Support', 'Advanced Analytics', 'Vehicle Tracking', 'Trip Management', 'Duty Slips', 'Custom Invoices'],
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional Plan',
                'description' => 'Advanced features for established transport companies',
                'price' => 699.00,
                'duration' => 'monthly',
                'billing_cycle_days' => 30,
                'max_vehicles' => 50,
                'max_trips_per_month' => 500,
                'max_staff' => 100,
                'features' => ['Priority Support', 'Advanced Analytics', 'API Access', 'Custom Branding', 'Staff Management', 'Inventory Tracking'],
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise Plan',
                'description' => 'Unlimited power for large-scale operations',
                'price' => 1499.00,
                'duration' => 'monthly',
                'billing_cycle_days' => 30,
                'max_vehicles' => null, // Unlimited
                'max_trips_per_month' => null, // Unlimited
                'max_staff' => null, // Unlimited
                'features' => ['24/7 Support', 'Custom Integration', 'Dedicated Manager', 'White Label Solution', 'Advanced Reporting', 'Custom API', 'Multi-tenant Support'],
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'name' => 'Yearly Basic',
                'description' => 'Starter plan billed yearly with discount',
                'price' => 1089.00,
                'duration' => 'yearly',
                'billing_cycle_days' => 365,
                'max_vehicles' => 5,
                'max_trips_per_month' => 50,
                'max_staff' => 10,
                'features' => ['Email Support', 'Basic Tracking', 'Mobile App'],
                'status' => 'active',
                'sort_order' => 5,
            ],
            [
                'name' => 'Lifetime Enterprise',
                'description' => 'One-time payment for lifetime access',
                'price' => 9999.00,
                'duration' => 'lifetime',
                'billing_cycle_days' => 999999,
                'max_vehicles' => null, // Unlimited
                'max_trips_per_month' => null, // Unlimited
                'max_staff' => null, // Unlimited
                'features' => ['24/7 Support', 'Custom Integration', 'Dedicated Manager', 'White Label Solution', 'Advanced Reporting', 'Lifetime Updates'],
                'status' => 'inactive',
                'sort_order' => 6,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
