<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffAttendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffAttendanceReportController extends Controller
{
    /**
     * Show staff attendance report page
     */
    public function index()
    {
        return Inertia::render('Admin/Reports/StaffAttendance', [
            'employees' => Employee::select('id', 'first_name', 'last_name', 'staff_number')
                ->orderBy('first_name')
                ->get()
                ->map(fn($emp) => [
                    'id' => $emp->id,
                    'name' => $emp->full_name,
                    'staff_number' => $emp->staff_number
                ]),
        ]);
    }

    /**
     * Get attendance data with filters
     */
    public function getData(Request $request)
    {
        $query = StaffAttendance::with('employee:id,first_name,last_name,staff_number');

        // Date range filter
        if ($request->start_date && $request->end_date) {
            $query->dateRange($request->start_date, $request->end_date);
        } else {
            // Default to current month
            $query->dateRange(
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            );
        }

        // Employee filter
        if ($request->employee_id) {
            $query->forEmployee($request->employee_id);
        }

        // Status filter
        if ($request->status) {
            $query->withStatus($request->status);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'employee_name' => $attendance->employee->full_name,
                    'staff_number' => $attendance->employee->staff_number,
                    'date' => $attendance->date->format('Y-m-d'),
                    'formatted_date' => $attendance->date->format('M d, Y'),
                    'clock_in' => $attendance->formatted_clock_in,
                    'clock_out' => $attendance->formatted_clock_out,
                    'total_hours' => $attendance->total_hours,
                    'status' => $attendance->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $attendance->status)),
                ];
            }),
            'pagination' => [
                'total' => $attendances->total(),
                'per_page' => $attendances->perPage(),
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
            ],
        ]);
    }

    /**
     * Get summary statistics
     */
    public function getSummary(Request $request)
    {
        $query = StaffAttendance::query();

        // Date range filter
        if ($request->start_date && $request->end_date) {
            $query->dateRange($request->start_date, $request->end_date);
        } else {
            $query->dateRange(
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            );
        }

        // Employee filter
        if ($request->employee_id) {
            $query->forEmployee($request->employee_id);
        }

        $total = $query->count();
        $present = $query->clone()->where('status', 'present')->count();
        $late = $query->clone()->where('status', 'late')->count();
        $halfDay = $query->clone()->where('status', 'half_day')->count();

        return response()->json([
            'total_records' => $total,
            'present' => $present,
            'late' => $late,
            'half_day' => $halfDay,
            'present_percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            'late_percentage' => $total > 0 ? round(($late / $total) * 100, 1) : 0,
        ]);
    }

    /**
     * Export attendance report as PDF
     */
    public function export(Request $request)
    {
        $query = StaffAttendance::with('employee:id,first_name,last_name,staff_number');

        // Apply filters
        if ($request->start_date && $request->end_date) {
            $query->dateRange($request->start_date, $request->end_date);
            $startDate = Carbon::parse($request->start_date)->format('M d, Y');
            $endDate = Carbon::parse($request->end_date)->format('M d, Y');
        } else {
            $query->dateRange(
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            );
            $startDate = Carbon::now()->startOfMonth()->format('M d, Y');
            $endDate = Carbon::now()->endOfMonth()->format('M d, Y');
        }

        if ($request->employee_id) {
            $query->forEmployee($request->employee_id);
        }

        if ($request->status) {
            $query->withStatus($request->status);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->get();

        // Calculate summary statistics
        $total = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'present')->where('is_late', true)->count();
        $halfDayCount = $attendances->where('status', 'half_day')->count();
        $absentCount = $attendances->where('status', 'absent')->count();

        $data = [
            'attendances' => $attendances,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'generated_at' => Carbon::now()->format('M d, Y h:i A'),
            'summary' => [
                'total' => $total,
                'present' => $presentCount,
                'late' => $lateCount,
                'half_day' => $halfDayCount,
                'absent' => $absentCount,
            ],
        ];

        $pdf = \PDF::loadView('reports.staff-attendance', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('staff_attendance_' . date('Y-m-d_His') . '.pdf');
    }
}
