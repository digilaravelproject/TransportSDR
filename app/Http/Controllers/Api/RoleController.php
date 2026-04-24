<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoleModule;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin']);

        $request->validate([
            'search'    => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $roles = RoleModule::query()
                ->when(
                    $request->search,
                    fn($q, $v) => $q->where(function ($query) use ($v) {
                        $query->where('name', 'like', "%{$v}%")
                            ->orWhere('description', 'like', "%{$v}%");
                    })
                )
                ->when(
                    $request->filled('is_active'),
                    fn($q) => $q->where('is_active', $request->boolean('is_active'))
                )
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $roles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'is_active'    => 'nullable|boolean',
            'features'     => 'nullable|array',
            'features.*'   => 'string|max:255',
        ]);

        try {
            $exists = RoleModule::where('name', $data['name'])->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role name already exists.',
                ], 422);
            }

            $role = RoleModule::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active'   => $data['is_active'] ?? true,
                'features'    => $data['features'] ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data'    => $role,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(RoleModule $role)
    {
        $this->checkRole(['superadmin', 'admin']);

        return response()->json([
            'success' => true,
            'data'    => $role,
        ]);
    }

    public function update(Request $request, RoleModule $role)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'description'  => 'nullable|string',
            'is_active'    => 'nullable|boolean',
            'features'     => 'nullable|array',
            'features.*'   => 'string|max:255',
        ]);

        try {
            if (isset($data['name'])) {
                $exists = RoleModule::where('name', $data['name'])
                    ->where('id', '!=', $role->id)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Role name already exists.',
                    ], 422);
                }
            }

            $role->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'data'    => $role->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(RoleModule $role)
    {
        $this->checkRole(['superadmin', 'admin']);

        try {
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }
    public function indexrole()
    {
        $roles = RoleModule::where('is_active', true)->get(['id', 'name']);
        return response()->json(['success' => true, 'data' => $roles]);
    }
}
