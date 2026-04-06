<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Models\{Tenant, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $tenants = Tenant::withCount(['users', 'trips', 'vehicles'])
            ->when($request->plan,     fn($q, $v) => $q->where('plan', $v))
            ->when($request->is_active, fn($q, $v) => $q->where('is_active', (bool)$v))
            ->when($request->search,   fn($q, $v) => $q->where('company_name', 'like', "%$v%"))
            ->latest()->paginate(20)->withQueryString();

        return response()->json(['success' => true, 'data' => $tenants]);
    }

    public function store(StoreTenantRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'company_name'        => $request->company_name,
                'email'               => $request->email,
                'phone'               => $request->phone,
                'gstin'               => $request->gstin,
                'address'             => $request->address,
                'plan'                => $request->plan,
                'max_vehicles'        => $request->max_vehicles,
                'max_trips_per_month' => $request->max_trips_per_month,
                'plan_expires_at'     => $request->plan_expires_at,
                'is_active'           => true,
            ]);

            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $request->admin_name,
                'email'     => $request->email,
                'password'  => Hash::make($request->admin_password),
                'role'      => 'admin',
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Admin account created successfully.',
                'data'    => ['tenant' => $tenant, 'admin' => $admin->only('id', 'name', 'email', 'role')],
            ], 201);
        });
    }

    public function show(Tenant $tenant)
    {
        $tenant->loadCount(['users', 'trips', 'vehicles']);
        return response()->json(['success' => true, 'data' => $tenant]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'company_name'        => 'sometimes|string|max:255',
            'phone'               => 'sometimes|string|max:15',
            'gstin'               => 'nullable|string|max:15',
            'plan'                => 'sometimes|in:basic,pro,enterprise',
            'max_vehicles'        => 'sometimes|integer|min:1',
            'max_trips_per_month' => 'sometimes|integer|min:1',
            'plan_expires_at'     => 'nullable|date',
        ]);
        $tenant->update($data);
        return response()->json(['success' => true, 'message' => 'Tenant updated.', 'data' => $tenant]);
    }

    public function suspend(Tenant $tenant)
    {
        $tenant->update(['is_active' => false]);
        return response()->json(['success' => true, 'message' => 'Tenant suspended.']);
    }

    public function activate(Tenant $tenant)
    {
        $tenant->update(['is_active' => true]);
        return response()->json(['success' => true, 'message' => 'Tenant activated.']);
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return response()->json(['success' => true, 'message' => 'Tenant deleted.']);
    }

    public function stats()
    {
        return response()->json(['success' => true, 'data' => [
            'total_tenants'  => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'by_plan'        => Tenant::selectRaw('plan, count(*) as count')->groupBy('plan')->get(),
            'total_trips'    => DB::table('trips')->count(),
            'total_vehicles' => DB::table('vehicles')->count(),
        ]]);
    }

    // Admin ke liye sub-users banana
    public function createUser(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,operator,driver,accountant',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'User created.', 'data' => $user], 201);
    }
}
