<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Rank;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user();
        $teacher = Teacher::where('employee_id', $employee->id)->first();
        $rank = $teacher->load('rank');
        
        return Inertia::render("Employee/Attendance/Index", [
            "teacher" => $teacher,
            "rank" => $rank
        ]);
    }
    
    public function store(StoreAttendanceRequest $request)
    {
        $validated = $request->validated();
        
        DB::beginTransaction();
        
        try {
            foreach ($validated['attendances'] as $attendance)  {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $attendance['student_id'],
                        'rank_id' => $validated['rank_id'],
                        'date' => $validated['date'],
                    ],
                    [
                        'teacher_id' => $validated['teacher_id'],
                        'status' => $attendance['status'],
                        'remarks' => $attendance['remarks'] ?? null,
                    ]
                );
            }
            DB::commit();
            return back(303)->with('Attendance record created.');
            
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => $exception->getMessage()]);
        }
    }
    
    public function report()
    {
        $employee = Auth::guard('employee')->user();
        $teacher = Teacher::where('employee_id', $employee->id)->first();
        $rank = $teacher->load('rank');
        
        return Inertia::render("Employee/Attendance/Report", [
            "teacher" => $teacher,
            "rank" => $rank
        ]);
    }
    
    public function fetchForDate(Request $request)
    {
        $rankId = $request->query('rank_id');
        $date = $request->query('date');
        if(!$rankId) {
            return back()->withErrors(['message' => 'Class Id required']);
        };
        
        $attendances = Attendance::where('rank_id', $rankId)
            ->where('date', $date)
            ->get();
        $attendances->load('student', 'teacher', 'rank');
        
        return response()->json([
            'attendances' => $attendances,
        ]);
    }
}
