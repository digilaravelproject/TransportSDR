<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Exception;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 1. Display staff list with attendance for a date
    public function index(Request $request)
    {
        try {
            $date = $request->query('date', Carbon::today()->toDateString());

            $staffs = Staff::where('is_active', true)->get(['id', 'name', 'phone', 'staff_type']);

            $data = $staffs->map(function ($s) use ($date) {
                $att = StaffAttendance::where('staff_id', $s->id)->where('date', $date)->first();
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'phone' => $s->phone,
                    'staff_type' => $s->staff_type,
                    'attendance' => $att ? [
                        'status' => $att->status,
                        'in_time' => $att->in_time,
                        'out_time' => $att->out_time,
                        'total_hours' => $att->total_hours,
                    ] : null
                ];
            });

            return response()->json(['success' => true, 'data' => $data], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch attendance', 'error' => $e->getMessage()], 500);
        }
    }

    // 2. Save attendance (bulk)
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'date' => 'required|date',
                'records' => 'required|array|min:1',
                'records.*.staff_id' => 'required|exists:staff,id',
                'records.*.status' => 'required|in:present,absent,half',
                'records.*.in_time' => 'nullable|date_format:H:i',
                'records.*.out_time' => 'nullable|date_format:H:i',
                'records.*.notes' => 'nullable|string',
            ]);

            foreach ($data['records'] as $rec) {
                $total = null;
                if (!empty($rec['in_time']) && !empty($rec['out_time'])) {
                    $in = Carbon::createFromFormat('H:i', $rec['in_time']);
                    $out = Carbon::createFromFormat('H:i', $rec['out_time']);
                    $minutes = $in->diffInMinutes($out);
                    $total = round($minutes / 60, 2);
                }

                StaffAttendance::updateOrCreate(
                    ['staff_id' => $rec['staff_id'], 'date' => $data['date']],
                    [
                        'status' => $rec['status'],
                        'in_time' => $rec['in_time'] ?? null,
                        'out_time' => $rec['out_time'] ?? null,
                        'total_hours' => $total,
                        'notes' => $rec['notes'] ?? null,
                    ]
                );
            }

            return response()->json(['success' => true, 'message' => 'Attendance saved successfully'], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to save attendance', 'error' => $e->getMessage()], 500);
        }
    }

    // 3. Get attendance records for a staff (datewise)
    public function staffRecords($staffId, Request $request)
    {
        try {
            $start = $request->query('start');
            $end = $request->query('end');

            if (!$start || !$end) {
                // default last 30 days
                $end = Carbon::today()->toDateString();
                $start = Carbon::today()->subDays(30)->toDateString();
            }

            $records = StaffAttendance::where('staff_id', $staffId)
                ->whereBetween('date', [$start, $end])
                ->orderBy('date', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $records], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch staff records', 'error' => $e->getMessage()], 500);
        }
    }

    // 4. Search attendance by staff name (optional date)
    public function search(Request $request)
    {
        try {
            $q = $request->query('query');
            $date = $request->query('date');

            $staffs = Staff::where('name', 'like', "%{$q}%")->get(['id', 'name', 'phone']);

            $data = $staffs->map(function ($s) use ($date) {
                $att = null;
                if ($date) {
                    $att = StaffAttendance::where('staff_id', $s->id)->where('date', $date)->first();
                }
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'phone' => $s->phone,
                    'attendance' => $att ? $att : null,
                ];
            });

            return response()->json(['success' => true, 'data' => $data], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to search attendance', 'error' => $e->getMessage()], 500);
        }
    }
}
