<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Vehicle;
use App\Models\Staff;
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
}
