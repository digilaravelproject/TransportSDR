<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    // GET /api/v1/leads
    public function index(Request $request)
    {
        $query = Lead::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('trip_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('trip_date', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('customer_name', 'like', "%{$s}%")
                    ->orWhere('customer_contact', 'like', "%{$s}%")
                    ->orWhere('trip_route', 'like', "%{$s}%")
                    ->orWhere('lead_number', 'like', "%{$s}%");
            });
        }

        $perPage = (int) ($request->per_page ?? 20);
        $leads = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'data'    => $leads->items(),
            'meta'    => [
                'total' => $leads->total(),
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
            ],
        ]);
    }

    // POST /api/v1/leads
    public function store(Request $request)
    {
        $rules = [
            'trip_route' => 'required|string',
            'trip_date' => 'required|date',
            'duration_days' => 'required|integer|min:1',
            'vehicle_type' => 'required|string',
            'seating_capacity' => 'required|integer|min:1',
            'pickup_address' => 'required|string',
            'points' => 'required|array|min:1',
            'points.*.type' => 'required|string',
            'points.*.name' => 'required|string',
            'points.*.lat' => 'required|numeric',
            'points.*.lng' => 'required|numeric',
            'points.*.order' => 'required|integer',
            'customer_name' => 'required|string',
            'customer_contact' => 'required|string',
            'total_amount' => 'required|numeric',
            'advance_amount' => 'sometimes|numeric',
            'pending_amount' => 'sometimes|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Ensure numeric defaults
        $data['advance_amount'] = $data['advance_amount'] ?? 0;
        $data['pending_amount'] = $data['pending_amount'] ?? max(0, ($data['total_amount'] - $data['advance_amount']));
        $data['tenant_id'] = auth()->user()->tenant_id ?? null;

        $lead = Lead::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully.',
            'data' => $lead,
        ], 201);
    }

    // GET /api/v1/leads/{lead}
    public function show(Lead $lead)
    {
        return response()->json(['success' => true, 'data' => $lead]);
    }
}
