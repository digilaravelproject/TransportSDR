<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Vehicle;
use App\Models\Staff;
use App\Models\LeadExpense;
use App\Models\DutySheet;
use App\Models\LeadNote;
use App\Models\LeadFollowup;
use App\Models\Trip;
use App\Services\Notification\NotificationService;

class AdminLeadController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $query = Lead::query()->with(['vehicle','driver']);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where('customer_name','like','%'.$request->search.'%');

        $leads = $query->orderBy('created_at','desc')->paginate(20);

        return view('admin.leads.index', compact('leads'));
    }

    public function show(Lead $lead)
    {
        $lead->load(['notes.author','followups.author','expenses.creator','dutySheets','vehicle','driver']);
        $vehicles = Vehicle::available()->get();
        $drivers = Staff::drivers()->available()->get();
        return view('admin.leads.show', compact('lead','vehicles','drivers'));
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'customer_name' => 'sometimes|string',
            'customer_contact' => 'sometimes|string',
            'trip_route' => 'sometimes|string',
            'trip_date' => 'sometimes|date',
            'total_amount' => 'sometimes|numeric'
        ]);
        $lead->update($data);
        try { $this->notificationService->create('Lead Updated', "Lead {$lead->lead_number} updated by admin"); } catch (\Throwable $e) {}
        return redirect()->route('admin.leads.show', $lead)->with('success','Lead updated');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        try { $this->notificationService->create('Lead Deleted', "Lead {$lead->lead_number} deleted by admin"); } catch (\Throwable $e) {}
        return redirect()->route('admin.leads.index')->with('success','Lead deleted');
    }

    public function assignVehicle(Request $request, Lead $lead)
    {
        $data = $request->validate(['vehicle_id' => 'required|exists:vehicles,id']);
        if ($lead->vehicle_id) Vehicle::find($lead->vehicle_id)?->update(['is_available' => true]);
        Vehicle::find($data['vehicle_id'])?->update(['is_available' => false]);
        $lead->update(['vehicle_id' => $data['vehicle_id']]);
        try { $this->notificationService->create('Vehicle Assigned', "Vehicle assigned to {$lead->lead_number} by admin"); } catch (\Throwable $e) {}
        return back()->with('success','Vehicle assigned');
    }

    public function assignDriver(Request $request, Lead $lead)
    {
        $data = $request->validate(['driver_id' => 'required|exists:staff,id']);
        if ($lead->driver_id) Staff::find($lead->driver_id)?->update(['is_available' => true]);
        Staff::find($data['driver_id'])?->update(['is_available' => false]);
        $lead->update(['driver_id' => $data['driver_id']]);
        try { $this->notificationService->create('Driver Assigned', "Driver assigned to {$lead->lead_number} by admin"); } catch (\Throwable $e) {}
        return back()->with('success','Driver assigned');
    }

    public function addExpense(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'expense_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);
        
        $data['lead_id'] = $lead->id;
        $data['created_by'] = auth()->id();
        
        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('leads/expenses', 'public');
        }
        
        LeadExpense::create($data);
        return back()->with('success', 'Expense added');
    }

    public function uploadDutySheet(Request $request, Lead $lead)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string'
        ]);
        
        DutySheet::create([
            'lead_id' => $lead->id,
            'file_path' => $request->file('file')->store('leads/duty_sheets', 'public'),
            'notes' => $request->notes,
            'uploaded_by' => auth()->id()
        ]);
        
        return back()->with('success', 'Duty sheet uploaded');
    }

    public function addNote(Request $request, Lead $lead)
    {
        $request->validate(['note' => 'required|string']);
        LeadNote::create([
            'lead_id' => $lead->id,
            'note' => $request->note,
            'created_by' => auth()->id()
        ]);
        return back()->with('success', 'Note added');
    }

    public function addFollowup(Request $request, Lead $lead)
    {
        $request->validate([
            'followup_date' => 'required|date',
            'notes' => 'required|string'
        ]);
        LeadFollowup::create([
            'lead_id' => $lead->id,
            'followup_date' => $request->followup_date,
            'notes' => $request->notes,
            'created_by' => auth()->id()
        ]);
        return back()->with('success', 'Follow-up added');
    }

    public function convertToTrip(Request $request, Lead $lead)
    {
        if ($lead->status === 'converted') {
            return back()->with('error', 'Lead is already converted');
        }

        // Extremely basic conversion to match API
        $trip = Trip::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'customer_name' => $lead->customer_name,
            'customer_phone' => $lead->customer_contact,
            'vehicle_id' => $lead->vehicle_id,
            'driver_id' => $lead->driver_id,
            'pickup_address' => $lead->trip_route,
            'trip_date' => $lead->trip_date,
            'total_amount' => $lead->total_amount ?? 0,
            'status' => 'scheduled',
            'created_by' => auth()->id()
        ]);

        $lead->update(['status' => 'converted']);
        
        return back()->with('success', 'Lead converted to trip successfully');
    }
}
