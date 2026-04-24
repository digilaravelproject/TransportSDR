<?php

namespace App\Services;

use App\Models\{Staff, StaffAttendance, StaffSalary, StaffAdvance, StaffDaLog, Trip, StaffDocument};
use Illuminate\Support\Facades\{DB, File, Storage};
use Carbon\Carbon;

class StaffService
{
    // ── Create Staff ──────────────────────────────────
    public function store(array $data): Staff
    {
        return Staff::create($data);
    }

    // ── Update Staff ──────────────────────────────────
    public function update(Staff $staff, array $data): Staff
    {
        $staff->update($data);
        return $staff->fresh();
    }

    // ── Mark Attendance ───────────────────────────────
    // public function markAttendance(Staff $staff, array $data): StaffAttendance
    // {
    //     return StaffAttendance::updateOrCreate(
    //         [
    //             'staff_id'  => $staff->id,
    //             'tenant_id' => $staff->tenant_id,
    //             'date'      => $data['date'],
    //         ],
    //         $data
    //     );
    // }
    // Mark Attendance method me trip_purpose add karein
    public function markAttendance(Staff $staff, array $data): StaffAttendance
    {
        return StaffAttendance::updateOrCreate(
            ['staff_id' => $staff->id, 'date' => $data['date']],
            [
                'trip_purpose' => $data['trip_purpose'] ?? null,
                'status' => $data['status'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'notes' => $data['notes'] ?? null
            ]
        );
    }

    // ── Calculate DA for a trip ───────────────────────
    public function calculateTripDA(Staff $staff, Trip $trip): StaffDaLog
    {
        // Check already exists
        $existing = StaffDaLog::where('staff_id', $staff->id)
            ->where('trip_id', $trip->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $daPerDay = $staff->da_per_day ?? 0;
        $days     = $trip->duration_days ?? 1;

        return StaffDaLog::create([
            'staff_id'       => $staff->id,
            'trip_id'        => $trip->id,
            'trip_days'      => $days,
            'da_per_day'     => $daPerDay,
            'extra_allowance' => 0,
            'status'         => 'pending',
        ]);
    }

    // ── Generate Monthly Salary ───────────────────────
    public function generateSalary(Staff $staff, int $year, int $month): StaffSalary
    {
        $monthStr  = str_pad($month, 2, '0', STR_PAD_LEFT);
        $period    = "{$year}-{$monthStr}";
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $totalDays = $endDate->day;

        // Attendance summary for this month
        $attendance = StaffAttendance::where('staff_id', $staff->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $presentDays = $attendance->whereIn('status', ['present', 'on_trip'])->count();
        $absentDays  = $attendance->where('status', 'absent')->count();
        $halfDays    = $attendance->where('status', 'half_day')->count();
        $tripDays    = $attendance->where('status', 'on_trip')->count();

        // DA total for this month from trip logs
        $daTotal = StaffDaLog::where('staff_id', $staff->id)
            ->where('status', 'pending')
            ->whereHas('trip', fn($q) => $q->whereBetween('trip_date', [$startDate, $endDate]))
            ->sum('da_amount');

        // Per day salary
        $perDaySalary  = $totalDays > 0 ? $staff->basic_salary / $totalDays : 0;
        $absentDeduct  = round($perDaySalary * $absentDays, 2);
        $halfDayDeduct = round($perDaySalary * 0.5 * $halfDays, 2);
        $totalAbsent   = $absentDeduct + $halfDayDeduct;

        // Pending advance deduction
        $pendingAdvance = $staff->advances()
            ->where('is_deducted', false)
            ->sum('amount');

        // Check if salary already exists
        $salary = StaffSalary::where('staff_id', $staff->id)
            ->where('month', $period)
            ->where('year', $year)
            ->first();

        $salaryData = [
            'basic_salary'      => $staff->basic_salary,
            'hra'               => $staff->hra,
            'da_total'          => $daTotal,
            'other_allowance'   => $staff->other_allowance,
            'absent_deduction'  => $totalAbsent,
            'advance_deduction' => $pendingAdvance,
            'total_days'        => $totalDays,
            'present_days'      => $presentDays,
            'absent_days'       => $absentDays,
            'half_days'         => $halfDays,
            'trip_days'         => $tripDays,
        ];

        if ($salary) {
            $salary->update($salaryData);
            return $salary->fresh();
        }

        return StaffSalary::create(array_merge($salaryData, [
            'staff_id' => $staff->id,
            'month'    => $period,
            'year'     => $year,
        ]));
    }

    // ── Mark Salary Paid ──────────────────────────────
    public function markSalaryPaid(StaffSalary $salary, array $data): StaffSalary
    {
        return DB::transaction(function () use ($salary, $data) {
            $salary->update([
                'payment_status'  => 'paid',
                'payment_mode'    => $data['payment_mode'],
                'paid_on'         => $data['paid_on'],
                'transaction_ref' => $data['transaction_ref'] ?? null,
                'notes'           => $data['notes'] ?? null,
            ]);

            // Mark related advances as deducted
            if ($salary->advance_deduction > 0) {
                $advances = StaffAdvance::where('staff_id', $salary->staff_id)
                    ->where('is_deducted', false)
                    ->get();

                foreach ($advances as $advance) {
                    $advance->update([
                        'is_deducted' => true,
                        'salary_id'   => $salary->id,
                        'deducted_on' => $data['paid_on'],
                    ]);
                }
            }

            // Mark DA logs as paid
            StaffDaLog::where('staff_id', $salary->staff_id)
                ->where('status', 'pending')
                ->update([
                    'status'  => 'paid',
                    'paid_on' => $data['paid_on'],
                ]);

            return $salary->fresh();
        });
    }

    // ── Upload Document ───────────────────────────────
    public function uploadDocument(Staff $staff, array $data, $file): \App\Models\StaffDocument
    {
        $fileName = "staff-{$staff->id}-{$data['document_type']}-" . time() . '.' . $file->extension();
        $dir      = "tenants/{$staff->tenant_id}/staff-docs/{$staff->id}";
        $path     = $file->storeAs($dir, $fileName, 'public');

        return \App\Models\StaffDocument::create(array_merge($data, [
            'staff_id'      => $staff->id,
            'document_path' => $path,
        ]));
    }



    public function storeWithDocuments(array $data, $request): Staff
    {
        return DB::transaction(function () use ($data, $request) {
            // 1. Pehle staff ki basic details save karo
            $staff = Staff::create($data);

            // 2. Aadhar Card Upload
            if ($request->hasFile('aadhar_file')) {
                $this->saveDocumentRecord($staff, 'aadhar', $data['aadhar_number'] ?? null, null, $request->file('aadhar_file'));
            }

            // 3. PAN Card Upload
            if ($request->hasFile('pan_file')) {
                $this->saveDocumentRecord($staff, 'pan', $data['pan_number'] ?? null, null, $request->file('pan_file'));
            }

            // 4. Driving License Upload
            // if ($request->hasFile('dl_file')) {
            //     $this->saveDocumentRecord($staff, 'driving_license', $data['dl_number'] ?? null, $data['dl_expiry'] ?? null, $request->file('dl_file'));
            // }
            if ($request->hasFile('dl_file')) {
                $this->saveDocumentRecord($staff, 'license', $data['dl_number'] ?? null, $data['dl_expiry'] ?? null, $request->file('dl_file'));
            }

            // 5. Badge Upload
            if ($request->hasFile('badge_file')) {
                $this->saveDocumentRecord($staff, 'badge', $data['badge_number'] ?? null, $data['badge_expiry'] ?? null, $request->file('badge_file'));
            }

            // 6. Bank Passbook Upload
            if ($request->hasFile('passbook_file')) {
                $this->saveDocumentRecord($staff, 'bank_passbook', null, null, $request->file('passbook_file'));
            }

            // 7. Passport Size Photo Upload
            if ($request->hasFile('photo_file')) {
                $this->saveDocumentRecord($staff, 'photo', null, null, $request->file('photo_file'));
            }

            return $staff->load('documents');
        });
    }

    // Helper method for saving documents
    private function saveDocumentRecord(Staff $staff, string $type, ?string $number, ?string $expiry, $file)
    {
        $fileName = "staff-{$staff->id}-{$type}-" . time() . '.' . $file->extension();
        $dir      = "tenants/{$staff->tenant_id}/staff-docs/{$staff->id}";
        $path     = $file->storeAs($dir, $fileName, 'public');

        StaffDocument::create([
            'tenant_id'       => $staff->tenant_id,
            'staff_id'        => $staff->id,
            'document_type'   => $type,
            'document_number' => $number,
            'expiry_date'     => $expiry,
            'document_path'   => $path,
            'created_by'      => auth()->id(),
        ]);
    }

    public function updateWithDocuments(Staff $staff, array $data, $request): Staff
    {
        return DB::transaction(function () use ($staff, $data, $request) {
            // 1. Update basic details
            $staff->update($data);

            // 2. Aadhar Card Upload
            if ($request->hasFile('aadhar_file')) {
                $this->saveOrUpdateDocument($staff, 'aadhar', $data['aadhar_number'] ?? null, null, $request->file('aadhar_file'));
            }

            // 3. PAN Card Upload
            if ($request->hasFile('pan_file')) {
                $this->saveOrUpdateDocument($staff, 'pan', $data['pan_number'] ?? null, null, $request->file('pan_file'));
            }

            // 4. Driving License Upload
            if ($request->hasFile('dl_file')) {
                $this->saveOrUpdateDocument($staff, 'license', $data['dl_number'] ?? null, $data['dl_expiry'] ?? null, $request->file('dl_file'));
            }

            // 5. Badge Upload
            if ($request->hasFile('badge_file')) {
                $this->saveOrUpdateDocument($staff, 'badge', $data['badge_number'] ?? null, $data['badge_expiry'] ?? null, $request->file('badge_file'));
            }

            // 6. Bank Passbook
            if ($request->hasFile('passbook_file')) {
                $this->saveOrUpdateDocument($staff, 'bank_passbook', null, null, $request->file('passbook_file'));
            }

            // 7. Photo
            if ($request->hasFile('photo_file')) {
                $this->saveOrUpdateDocument($staff, 'photo', null, null, $request->file('photo_file'));
            }

            return $staff->fresh('documents');
        });
    }

    // Yeh naya helper updateOrCreate use karta hai, taaki purana document update ho jaye agar wapas upload ho toh
    private function saveOrUpdateDocument(Staff $staff, string $type, ?string $number, ?string $expiry, $file)
    {
        $fileName = "staff-{$staff->id}-{$type}-" . time() . '.' . $file->extension();
        $dir      = "tenants/{$staff->tenant_id}/staff-docs/{$staff->id}";
        $path     = $file->storeAs($dir, $fileName, 'public');

        \App\Models\StaffDocument::updateOrCreate(
            [
                'tenant_id'     => $staff->tenant_id,
                'staff_id'      => $staff->id,
                'document_type' => $type, // Pehle check karega is type ka document hai ya nahi
            ],
            [
                'document_number' => $number,
                'expiry_date'     => $expiry,
                'document_path'   => $path,
                'created_by'      => auth()->id(),
            ]
        );
    }
}
