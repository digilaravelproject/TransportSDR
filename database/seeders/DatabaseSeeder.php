<?php

namespace Database\Seeders;

use App\Models\{User, Tenant, Customer, Vehicle, Staff};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminSeeder::class);

        // 1. Super Admin
        User::create([
            'tenant_id' => null,
            'name'      => 'Super Admin',
            'email'     => 'super@admin.com',
            'password'  => Hash::make('Password@123'),
            'role'      => 'superadmin',
            'is_active' => true,
        ]);

        // 2. Test Tenant
        $tenant = Tenant::create([
            'company_name'        => 'Shiv Travels Lucknow',
            'email'               => 'admin@shivtravels.com',
            'phone'               => '9876543210',
            'gstin'               => '09AAAAA0000A1Z5',
            'plan'                => 'pro',
            'max_vehicles'        => 20,
            'max_trips_per_month' => 200,
            'is_active'           => true,
            'plan_expires_at'     => now()->addYear(),
        ]);

        // 3. Admin for tenant
        User::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Ramesh Admin',
            'email'     => 'admin@shivtravels.com',
            'password'  => Hash::make('Password@123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // 4. Operator
        $opUser = User::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Suresh Operator',
            'email'     => 'operator@shivtravels.com',
            'password'  => Hash::make('Password@123'),
            'role'      => 'operator',
            'is_active' => true,
        ]);

        // 5. Driver user + staff record
        $driverUser = User::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Mohan Driver',
            'email'     => 'driver@shivtravels.com',
            'password'  => Hash::make('Password@123'),
            'role'      => 'driver',
            'is_active' => true,
        ]);

        Staff::create([
            'tenant_id'      => $tenant->id,
            'user_id'        => $driverUser->id,
            'name'           => 'Mohan Driver',
            'phone'          => '9111111111',
            'staff_type'     => 'driver',
            'license_number' => 'UP32-20200012345',
            'license_expiry' => now()->addYears(3),
            'is_available'   => true,
            'is_active'      => true,
        ]);

        // 6. Vehicle
        Vehicle::create([
            'tenant_id'           => $tenant->id,
            'registration_number' => 'UP32AB1234',
            'type'                => 'bus',
            'seating_capacity'    => 32,
            'make'                => 'Tata',
            'model'               => 'LP 909',
            'fuel_type'           => 'diesel',
            'current_km'          => 45000,
            'is_available'        => true,
            'is_active'           => true,
        ]);

        // 7. Customer
        Customer::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Rahul Sharma',
            'phone'     => '9888888888',
            'email'     => 'rahul@example.com',
            'address'   => 'Hazratganj, Lucknow',
            'is_active' => true,
        ]);
    }
}
