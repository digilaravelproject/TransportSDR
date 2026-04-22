<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Get all active plans with pagination
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->query('limit', 15);
            $status = $request->query('status');
            
            $query = Plan::query();
            
            if ($status && in_array($status, ['active', 'inactive'])) {
                $query->where('status', $status);
            } else {
                $query->active();
            }
            
            $plans = $query->ordered()->paginate($limit);
            
            return response()->json([
                'success' => true,
                'message' => 'Plans retrieved successfully',
                'data' => PlanResource::collection($plans),
                'pagination' => [
                    'total' => $plans->total(),
                    'count' => $plans->count(),
                    'per_page' => $plans->perPage(),
                    'current_page' => $plans->currentPage(),
                    'last_page' => $plans->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving plans',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get total count of plans
     */
    public function getTotalPlans()
    {
        try {
            $total = Plan::count();
            $active = Plan::active()->count();
            $inactive = Plan::inactive()->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Total plans count retrieved',
                'data' => [
                    'total_plans' => $total,
                    'active_plans' => $active,
                    'inactive_plans' => $inactive,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving total plans',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all plans list (non-paginated)
     */
    public function getTotalPlansList()
    {
        try {
            $status = request()->query('status');
            $query = Plan::query();
            
            if ($status && in_array($status, ['active', 'inactive'])) {
                $query->where('status', $status);
            } else {
                $query->active();
            }
            
            $plans = $query->ordered()->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Plans list retrieved successfully',
                'data' => PlanResource::collection($plans),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving plans',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single plan by ID
     */
    public function show($id)
    {
        try {
            $plan = Plan::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Plan retrieved successfully',
                'data' => new PlanResource($plan),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create new plan (Super Admin Only)
     */
    public function store(Request $request)
    {
        try {
            $this->checkRole(['superadmin']);
            
            $validated = $request->validate([
                'name' => 'required|string|unique:plans|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|in:monthly,yearly,lifetime',
                'billing_cycle_days' => 'required|integer|min:1',
                'max_vehicles' => 'nullable|integer|min:1',
                'max_trips_per_month' => 'nullable|integer|min:1',
                'max_staff' => 'nullable|integer|min:1',
                'features' => 'nullable|array',
                'status' => 'required|in:active,inactive',
                'sort_order' => 'nullable|integer|min:0',
            ]);
            
            $plan = Plan::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Plan created successfully',
                'data' => new PlanResource($plan),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating plan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update existing plan (Super Admin Only)
     */
    public function update(Request $request, $id)
    {
        try {
            $this->checkRole(['superadmin']);
            
            $plan = Plan::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|string|unique:plans,name,' . $id . '|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|numeric|min:0',
                'duration' => 'sometimes|in:monthly,yearly,lifetime',
                'billing_cycle_days' => 'sometimes|integer|min:1',
                'max_vehicles' => 'nullable|integer|min:1',
                'max_trips_per_month' => 'nullable|integer|min:1',
                'max_staff' => 'nullable|integer|min:1',
                'features' => 'nullable|array',
                'status' => 'sometimes|in:active,inactive',
                'sort_order' => 'nullable|integer|min:0',
            ]);
            
            $plan->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Plan updated successfully',
                'data' => new PlanResource($plan),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating plan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete plan (Super Admin Only)
     */
    public function destroy($id)
    {
        try {
            $this->checkRole(['superadmin']);
            
            $plan = Plan::findOrFail($id);
            $plan->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Plan deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting plan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check user role
     */
    private function checkRole($roles = [])
    {
        $user = auth()->user();
        if (!in_array($user->role ?? $user->type, $roles)) {
            throw new \Exception('Unauthorized');
        }
    }
}
