<?php
// app/Http/Controllers/SuperAdmin/TenantController.php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Tenant, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};

class TenantController extends Controller
{
    public function index(Request $request)
    {
        try {
            $tenants = Tenant::withCount(['users', 'trips', 'vehicles'])
                ->when($request->is_active, fn($q, $v) => $q->where('is_active', (bool)$v))
                ->when($request->search,    fn($q, $v) => $q->where('company_name', 'like', "%{$v}%"))
                ->latest()
                ->paginate(20)
                ->withQueryString();

            return response()->json(['success' => true, 'data' => $tenants]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'company_name' => 'required|string|max:255',
                'email'        => 'required|email|unique:users,email',
                'phone'        => 'required|string|max:15',
                'gstin'        => 'nullable|string|max:15',
                'address'      => 'nullable|string',
                'admin_name'   => 'required|string|max:255',
                'admin_password' => 'required|string|min:8',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $tenant = Tenant::create([
                    'company_name' => $request->company_name,
                    'email'        => $request->email,
                    'phone'        => $request->phone,
                    'gstin'        => $request->gstin   ?? null,
                    'address'      => $request->address ?? null,
                    'is_active'    => true,
                ]);

                $admin = User::create([
                    'tenant_id' => $tenant->id,
                    'name'      => $request->admin_name,
                    'email'     => $request->email,
                    'phone'     => $request->phone,
                    'password'  => Hash::make($request->admin_password),
                    'role'      => 'admin',
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Admin account created successfully.',
                    'data'    => [
                        'tenant' => $tenant,
                        'admin'  => $admin->only('id', 'name', 'email', 'phone', 'role'),
                    ],
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tenant.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Tenant $tenant)
    {
        try {
            $tenant->loadCount(['users', 'trips', 'vehicles']);
            return response()->json(['success' => true, 'data' => $tenant]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Tenant $tenant)
    {
        try {
            $data = $request->validate([
                'company_name' => 'sometimes|string|max:255',
                'phone'        => 'sometimes|string|max:15',
                'gstin'        => 'nullable|string|max:15',
                'address'      => 'nullable|string',
            ]);

            $tenant->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Tenant updated successfully.',
                'data'    => $tenant->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function suspend(Tenant $tenant)
    {
        try {
            $tenant->update(['is_active' => false]);
            return response()->json(['success' => true, 'message' => 'Tenant suspended successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function activate(Tenant $tenant)
    {
        try {
            $tenant->update(['is_active' => true]);
            return response()->json(['success' => true, 'message' => 'Tenant activated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Tenant $tenant)
    {
        try {
            $tenant->delete();
            return response()->json(['success' => true, 'message' => 'Tenant deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function stats()
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => [
                    'total_tenants'  => Tenant::count(),
                    'active_tenants' => Tenant::where('is_active', true)->count(),
                    'total_trips'    => \Illuminate\Support\Facades\DB::table('trips')->count(),
                    'total_vehicles' => \Illuminate\Support\Facades\DB::table('vehicles')->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createUser(Request $request, Tenant $tenant)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'phone'    => 'nullable|string|max:15',
                'password' => 'required|string|min:8',
                'role'     => 'required|in:admin,operator,driver,accountant',
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'password'  => Hash::make($data['password']),
                'role'      => $data['role'],
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'data'    => $user,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
