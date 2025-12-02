<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClockInRequest;
use App\Http\Requests\ClockOutRequest;
use App\Models\StaffAttendance;
use App\Services\GpsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffAttendanceController extends Controller
{
    protected $gpsService;

    public function __construct(GpsService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    /**
     * Get authenticated employee from either web or employee guard
     */
    protected function getAuthenticatedEmployee()
    {
        // Check employee guard first
        if (auth('employee')->check()) {
            return auth('employee')->user();
        }

        // Check web guard and get associated employee
        if (auth('web')->check()) {
            $user = auth('web')->user();
            if ($user && $user->employee) {
                return $user->employee;
            }
        }

        return null;
    }

    /**
     * Show attendance marking page
     */
    public function index()
    {
        $employee = $this->getAuthenticatedEmployee();

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found. Please contact administrator.');
        }

        return Inertia::render('Employee/Attendance/MarkAttendance', [
            'todayStatus' => $this->getTodayStatus(),
            'schoolCoordinates' => $this->gpsService->getSchoolCoordinates(),
        ]);
    }

    /**
     * Clock in
     */
    public function clockIn(ClockInRequest $request)
    {
        $employee = $this->getAuthenticatedEmployee();
        $today = Carbon::today();

        // Check if already clocked in today
        $existing = StaffAttendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already clocked in today.');
        }

        // Validate GPS location
        $gpsCheck = $this->gpsService->isWithinGeofence(
            $request->latitude,
            $request->longitude
        );

        if (!$gpsCheck['within_geofence']) {
            return back()->with(
                'error',
                'You must be within the school compound to clock in. ' .
                    'Distance: ' . $gpsCheck['distance'] . 'm (Max: ' . $gpsCheck['radius'] . 'm)'
            );
        }

        // Determine if late based on time
        $clockInTime = Carbon::now();
        $lateThreshold = Carbon::today()->setTime(8, 30); // 8:30 AM
        $isLate = $clockInTime->greaterThan($lateThreshold);

        // Create attendance record - always mark as 'present' when they clock in
        $attendance = StaffAttendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'clock_in_time' => $clockInTime,
            'clock_in_latitude' => $request->latitude,
            'clock_in_longitude' => $request->longitude,
            'status' => 'present',  // Always present when clocked in
            'is_late' => $isLate,   // Track lateness separately
            'notes' => $request->notes,
        ]);

        return back()->with(
            'success',
            'Clocked in successfully at ' . $clockInTime->format('h:i A') .
                ($isLate ? ' (Late)' : '')
        );
    }

    /**
     * Clock out
     */
    public function clockOut(ClockOutRequest $request)
    {
        $employee = $this->getAuthenticatedEmployee();
        $today = Carbon::today();

        // Find today's attendance
        $attendance = StaffAttendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'You have not clocked in today.');
        }

        if ($attendance->clock_out_time) {
            return back()->with('error', 'You have already clocked out today.');
        }

        // Validate GPS location (optional for clock out)
        $gpsCheck = $this->gpsService->isWithinGeofence(
            $request->latitude,
            $request->longitude
        );

        $clockOutTime = Carbon::now();

        // Update attendance record
        $attendance->update([
            'clock_out_time' => $clockOutTime,
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
            'notes' => $request->notes ?? $attendance->notes,
        ]);

        // Update status based on total hours
        $totalHours = $attendance->total_hours;
        if ($totalHours < 4) {
            $attendance->update(['status' => 'half_day']);
        }

        return back()->with(
            'success',
            'Clocked out successfully at ' . $clockOutTime->format('h:i A') .
                '. Total hours: ' . $totalHours
        );
    }

    /**
     * Get today's attendance status
     */
    public function getTodayStatus()
    {
        $employee = $this->getAuthenticatedEmployee();
        $today = Carbon::today();

        $attendance = StaffAttendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return [
                'has_clocked_in' => false,
                'has_clocked_out' => false,
            ];
        }

        return [
            'has_clocked_in' => true,
            'has_clocked_out' => $attendance->clock_out_time !== null,
            'clock_in_time' => $attendance->formatted_clock_in,
            'clock_out_time' => $attendance->formatted_clock_out,
            'status' => $attendance->status,
            'total_hours' => $attendance->total_hours,
        ];
    }

    /**
     * Get attendance history
     */
    public function getHistory(Request $request)
    {
        $employee = $this->getAuthenticatedEmployee();
        $days = $request->input('days', 30);

        $attendances = StaffAttendance::where('employee_id', $employee->id)
            ->where('date', '>=', Carbon::today()->subDays($days))
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($attendance) {
                return [
                    'date' => $attendance->date->format('Y-m-d'),
                    'formatted_date' => $attendance->date->format('M d, Y'),
                    'clock_in' => $attendance->formatted_clock_in,
                    'clock_out' => $attendance->formatted_clock_out,
                    'total_hours' => $attendance->total_hours,
                    'status' => $attendance->status,
                ];
            });

        return response()->json($attendances);
    }
}
