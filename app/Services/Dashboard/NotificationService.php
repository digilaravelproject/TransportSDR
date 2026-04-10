<?php
// app/Services/Dashboard/NotificationService.php
namespace App\Services\Dashboard;

use App\Models\{Lead, VehicleDocument, Staff, Vehicle, Trip, Corporate};
use App\Models\{StaffSalary, CorporatePayment};
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    private int $tenantId;

    public function __construct()
    {
        $this->tenantId = auth()->user()->tenant_id;
    }

    public function getAllNotifications(): array
    {
        $cacheKey = "dashboard:notifications:{$this->tenantId}:" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $notifications = [];

            // 1. Leads followup today
            foreach ($this->getLeadFollowups() as $item) {
                $notifications[] = $item;
            }

            // 2. Vehicle document expiry
            foreach ($this->getDocumentExpiry() as $item) {
                $notifications[] = $item;
            }

            // 3. Staff license expiry
            foreach ($this->getStaffLicenseExpiry() as $item) {
                $notifications[] = $item;
            }

            // 4. Pending salary payments
            foreach ($this->getPendingSalaries() as $item) {
                $notifications[] = $item;
            }

            // 5. Corporate payment pending
            foreach ($this->getCorporatePaymentsPending() as $item) {
                $notifications[] = $item;
            }

            // 6. Trip balance pending
            foreach ($this->getTripBalancePending() as $item) {
                $notifications[] = $item;
            }

            // 7. Vehicle maintenance due
            foreach ($this->getMaintenanceDue() as $item) {
                $notifications[] = $item;
            }

            // Sort by priority
            usort($notifications, fn($a, $b) => $b['priority'] <=> $a['priority']);

            return [
                'total'         => count($notifications),
                'urgent'        => count(array_filter($notifications, fn($n) => $n['priority'] === 'high')),
                'notifications' => $notifications,
            ];
        });
    }

    private function getLeadFollowups(): array
    {
        $leads = Lead::whereDate('followup_date', today())
            ->whereNotIn('status', ['converted', 'lost', 'cancelled'])
            ->with('creator')
            ->get();

        return $leads->map(fn($lead) => [
            'id'       => "lead-followup-{$lead->id}",
            'type'     => 'lead_followup',
            'priority' => 'high',
            'icon'     => 'lead',
            'title'    => "Followup Due: {$lead->customer_name}",
            'message'  => "Lead {$lead->lead_number} — {$lead->trip_route}",
            'meta'     => [
                'lead_id'    => $lead->id,
                'lead_number' => $lead->lead_number,
                'status'     => $lead->status,
            ],
            'created_at' => $lead->followup_date->toDateString(),
        ])->toArray();
    }

    private function getDocumentExpiry(): array
    {
        $docs = VehicleDocument::with('vehicle')
            ->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('is_expired', false)
            ->get();

        $expired = VehicleDocument::with('vehicle')
            ->where('is_expired', true)
            ->where('expiry_date', '>=', now()->subDays(30))
            ->get();

        $notifications = [];

        foreach ($expired as $doc) {
            $notifications[] = [
                'id'       => "doc-expired-{$doc->id}",
                'type'     => 'document_expired',
                'priority' => 'high',
                'icon'     => 'document',
                'title'    => "Document EXPIRED: {$doc->vehicle?->registration_number}",
                'message'  => strtoupper($doc->document_type) . " expired on {$doc->expiry_date->format('d-m-Y')}",
                'meta'     => ['vehicle_id' => $doc->vehicle_id, 'document_type' => $doc->document_type],
                'created_at' => $doc->expiry_date->toDateString(),
            ];
        }

        foreach ($docs as $doc) {
            $days = $doc->daysUntilExpiry();
            $notifications[] = [
                'id'       => "doc-expiring-{$doc->id}",
                'type'     => 'document_expiring',
                'priority' => $days <= 7 ? 'high' : 'medium',
                'icon'     => 'document',
                'title'    => "Document Expiring: {$doc->vehicle?->registration_number}",
                'message'  => strtoupper($doc->document_type) . " expires in {$days} days ({$doc->expiry_date->format('d-m-Y')})",
                'meta'     => ['vehicle_id' => $doc->vehicle_id, 'document_type' => $doc->document_type, 'days_left' => $days],
                'created_at' => $doc->expiry_date->toDateString(),
            ];
        }

        return $notifications;
    }

    private function getStaffLicenseExpiry(): array
    {
        $staff = Staff::where('staff_type', 'driver')
            ->whereNotNull('license_expiry')
            ->where('license_expiry', '>=', today())
            ->where('license_expiry', '<=', now()->addDays(30))
            ->get();

        $expired = Staff::where('staff_type', 'driver')
            ->whereNotNull('license_expiry')
            ->where('license_expiry', '<', today())
            ->get();

        $notifications = [];

        foreach ($expired as $s) {
            $notifications[] = [
                'id'       => "license-expired-{$s->id}",
                'type'     => 'license_expired',
                'priority' => 'high',
                'icon'     => 'staff',
                'title'    => "License EXPIRED: {$s->name}",
                'message'  => "Driver license expired on {$s->license_expiry->format('d-m-Y')}",
                'meta'     => ['staff_id' => $s->id, 'staff_name' => $s->name],
                'created_at' => $s->license_expiry->toDateString(),
            ];
        }

        foreach ($staff as $s) {
            $days = (int) now()->diffInDays($s->license_expiry, false);
            $notifications[] = [
                'id'       => "license-expiring-{$s->id}",
                'type'     => 'license_expiring',
                'priority' => $days <= 7 ? 'high' : 'medium',
                'icon'     => 'staff',
                'title'    => "License Expiring: {$s->name}",
                'message'  => "Driver license expires in {$days} days ({$s->license_expiry->format('d-m-Y')})",
                'meta'     => ['staff_id' => $s->id, 'days_left' => $days],
                'created_at' => $s->license_expiry->toDateString(),
            ];
        }

        return $notifications;
    }

    private function getPendingSalaries(): array
    {
        $salaries = StaffSalary::with('staff')
            ->where('payment_status', 'pending')
            ->where('month', '<', now()->format('Y-m'))
            ->get();

        return $salaries->map(fn($sal) => [
            'id'       => "salary-pending-{$sal->id}",
            'type'     => 'salary_pending',
            'priority' => 'medium',
            'icon'     => 'staff',
            'title'    => "Salary Pending: {$sal->staff?->name}",
            'message'  => "₹{$sal->net_salary} pending for {$sal->month}",
            'meta'     => ['staff_id' => $sal->staff_id, 'salary_id' => $sal->id, 'amount' => $sal->net_salary],
            'created_at' => $sal->created_at->toDateString(),
        ])->toArray();
    }

    private function getCorporatePaymentsPending(): array
    {
        $payments = CorporatePayment::with('corporate')
            ->where('payment_status', 'pending')
            ->orWhere('payment_status', 'partial')
            ->get();

        return $payments->map(fn($p) => [
            'id'       => "corp-payment-{$p->id}",
            'type'     => 'corporate_payment_pending',
            'priority' => 'medium',
            'icon'     => 'corporate',
            'title'    => "Payment Pending: {$p->corporate?->company_name}",
            'message'  => "₹{$p->balance_amount} pending — Invoice {$p->invoice_number}",
            'meta'     => ['corporate_id' => $p->corporate_id, 'payment_id' => $p->id, 'balance' => $p->balance_amount],
            'created_at' => $p->created_at->toDateString(),
        ])->toArray();
    }

    private function getTripBalancePending(): array
    {
        $trips = Trip::where('payment_status', 'partial')
            ->where('status', 'completed')
            ->where('balance_amount', '>', 0)
            ->orderByDesc('balance_amount')
            ->limit(10)
            ->get();

        return $trips->map(fn($trip) => [
            'id'       => "trip-balance-{$trip->id}",
            'type'     => 'trip_balance_pending',
            'priority' => 'low',
            'icon'     => 'trip',
            'title'    => "Balance Pending: {$trip->trip_number}",
            'message'  => "₹{$trip->balance_amount} pending from {$trip->customer_name}",
            'meta'     => ['trip_id' => $trip->id, 'balance' => $trip->balance_amount],
            'created_at' => $trip->trip_date->toDateString(),
        ])->toArray();
    }

    private function getMaintenanceDue(): array
    {
        $vehicles = Vehicle::whereHas(
            'trips',
            fn($q) =>
            $q->where('status', 'completed')
        )->get();

        $notifications = [];

        foreach ($vehicles as $vehicle) {
            $lastService = $vehicle->maintenanceLogs()
                ->where('status', 'completed')
                ->latest('service_date')
                ->first();

            if ($lastService && $lastService->next_service_date) {
                $daysLeft = (int) now()->diffInDays($lastService->next_service_date, false);

                if ($daysLeft <= 7) {
                    $notifications[] = [
                        'id'       => "maintenance-due-{$vehicle->id}",
                        'type'     => 'maintenance_due',
                        'priority' => $daysLeft < 0 ? 'high' : 'medium',
                        'icon'     => 'vehicle',
                        'title'    => "Service Due: {$vehicle->registration_number}",
                        'message'  => $daysLeft < 0
                            ? "Service overdue by " . abs($daysLeft) . " days"
                            : "Service due in {$daysLeft} days ({$lastService->next_service_date->format('d-m-Y')})",
                        'meta'     => ['vehicle_id' => $vehicle->id, 'days_left' => $daysLeft],
                        'created_at' => now()->toDateString(),
                    ];
                }
            }
        }

        return $notifications;
    }
}
