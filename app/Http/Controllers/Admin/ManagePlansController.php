<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class ManagePlansController extends Controller
{
    /**
     * Display all plans
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');
        
        $query = Plan::query();
        
        if ($status && in_array($status, ['active', 'inactive'])) {
            $query->where('status', $status);
        }
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        $plans = $query->ordered()->paginate(15);
        
        return view('admin.plans.index', compact('plans', 'status', 'search'));
    }

    /**
     * Show create plan form
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store new plan in database
     */
    public function store(Request $request)
    {
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

        return redirect()->route('admin.plans.index')
                        ->with('success', 'Plan created successfully!');
    }

    /**
     * Show edit plan form
     */
    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update plan in database
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:plans,name,' . $plan->id . '|max:255',
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

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
                        ->with('success', 'Plan updated successfully!');
    }

    /**
     * Delete plan
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')
                        ->with('success', 'Plan deleted successfully!');
    }

    /**
     * Show plan details
     */
    public function show(Plan $plan)
    {
        return view('admin.plans.show', compact('plan'));
    }
}

