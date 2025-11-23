<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Rank;
use App\Models\Teacher;
use App\Models\EmployeeClass;
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
        
        if (!$teacher) {
            return redirect()->back()->withErrors(['message' => 'Teacher record not found.']);
        }

        // Get the classes where this teacher is a CLASS TEACHER
        $classTeacherAssignments = EmployeeClass::where('employee_id', $employee->id)
            ->where('is_class_teacher', true)
            ->with(['class' => function($query) {
                $query->select('id', 'name', 'stream_id', 'division_id');
                $query->with([
                    'stream' => function($q) {
                        $q->select('id', 'name');
                    },
                    'division' => function($q) {
                        $q->select('id', 'name');
                    }
                ]);
            }])
            ->get();

        $classTeacherRankIds = $classTeacherAssignments->pluck('class_id')->toArray();

        $rank = Rank::whereIn('id', $classTeacherRankIds)
            ->select('id', 'name', 'stream_id', 'division_id', 'activated')
            ->with([
                'stream' => function($q) {
                    $q->select('id', 'name');
                },
                'division' => function($q) {
                    $q->select('id', 'name');
                }
            ])
            ->get();

        // Check if teacher is actually a class teacher for any class
        $isClassTeacher = $classTeacherAssignments->isNotEmpty();

        if (!$isClassTeacher) {
            return Inertia::render("Employee/Attendance/Index", [
                "teacher" => $teacher,
                "rank" => [],
                "isClassTeacher" => false,
                "error" => "You are not a class teacher, you cannot mark attendance. Once a class is assigned to you, you can mark the student attendance of your class."
            ]);
        }

        return Inertia::render("Employee/Attendance/Index", [
            "teacher" => $teacher,
            "rank" => $rank,
            "isClassTeacher" => true,
            "classTeacherAssignments" => $classTeacherAssignments
        ]);
    }
    
    public function store(StoreAttendanceRequest $request)
    {
        $validated = $request->validated();
        $employee = Auth::guard('employee')->user();

        $isClassTeacherForRank = EmployeeClass::where('employee_id', $employee->id)
            ->where('class_id', $validated['rank_id'])
            ->where('is_class_teacher', true)
            ->exists();

        if (!$isClassTeacherForRank) {
            return redirect()->back()->withErrors([
                'message' => 'You are not authorized to mark attendance for this class. Only class teachers can mark attendance.'
            ]);
        }

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
            return back(303)->with('success', 'Attendance record created successfully.');
            
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
        
        if (!$teacher) {
            return redirect()->back()->withErrors(['message' => 'Teacher record not found.']);
        }

        // Get classes where teacher is class teacher for reports
        $classTeacherAssignments = EmployeeClass::where('employee_id', $employee->id)
            ->where('is_class_teacher', true)
            ->with(['class' => function($query) {
                $query->select('id', 'name', 'stream_id', 'division_id');
                $query->with([
                    'stream' => function($q) {
                        $q->select('id', 'name');
                    },
                    'division' => function($q) {
                        $q->select('id', 'name');
                    }
                ]);
            }])
            ->get();

        $rank = Rank::whereIn('id', $classTeacherAssignments->pluck('class_id'))
            ->select('id', 'name', 'stream_id', 'division_id')
            ->with([
                'stream' => function($q) {
                    $q->select('id', 'name');
                },
                'division' => function($q) {
                    $q->select('id', 'name');
                }
            ])
            ->get();

        return Inertia::render("Employee/Attendance/Report", [
            "teacher" => $teacher,
            "rank" => $rank,
            "isClassTeacher" => $classTeacherAssignments->isNotEmpty()
        ]);
    }
    
    public function fetchForDate(Request $request)
    {
        $rankId = $request->query('rank_id');
        $date = $request->query('date');
        $employee = Auth::guard('employee')->user();

        if(!$rankId) {
            return response()->json([
                'error' => 'Class Id required'
            ], 400);
        }

        // Verify the teacher is class teacher for this rank
        $isClassTeacherForRank = EmployeeClass::where('employee_id', $employee->id)
            ->where('class_id', $rankId)
            ->where('is_class_teacher', true)
            ->exists();

        if (!$isClassTeacherForRank) {
            return response()->json([
                'error' => 'You are not authorized to view attendance for this class.'
            ], 403);
        }
        
        $attendances = Attendance::where('rank_id', $rankId)
            ->where('date', $date)
            ->get();
        $attendances->load('student', 'teacher', 'rank');
        
        return response()->json([
            'attendances' => $attendances,
        ]);
    }

    /**
     * Get students for a specific class/rank
     */
    public function getClassStudents(Request $request, $rankId)
    {
        $employee = Auth::guard('employee')->user();

        // Verify the teacher is class teacher for this rank
        $isClassTeacherForRank = EmployeeClass::where('employee_id', $employee->id)
            ->where('class_id', $rankId)
            ->where('is_class_teacher', true)
            ->exists();

        if (!$isClassTeacherForRank) {
            return response()->json([
                'error' => 'You are not authorized to view students for this class.'
            ], 403);
        }

        $rank = Rank::with(['students' => function($query) {
            $query->select('id', 'first_name', 'last_name', 'admission_number', 'gender_id', 'rank_id')
                  ->with(['gender' => function($q) {
                      $q->select('id', 'name');
                  }])
                  ->orderBy('first_name');
        }])->find($rankId);

        if (!$rank) {
            return response()->json([
                'error' => 'Class not found.'
            ], 404);
        }

        return response()->json([
            'rank' => $rank,
            'students' => $rank->students
        ]);
    }

    /**
     * Get class details for attendance marking
     */
    public function getClassDetails($rankId)
    {
        $employee = Auth::guard('employee')->user();
        $isClassTeacherForRank = EmployeeClass::where('employee_id', $employee->id)
            ->where('class_id', $rankId)
            ->where('is_class_teacher', true)
            ->exists();

        if (!$isClassTeacherForRank) {
            return response()->json([
                'error' => 'You are not authorized to access this class.'
            ], 403);
        }

        $rank = Rank::select('id', 'name', 'stream_id', 'division_id', 'activated')
            ->with([
                'stream' => function($q) {
                    $q->select('id', 'name');
                },
                'division' => function($q) {
                    $q->select('id', 'name');
                },
                'students' => function($query) {
                    $query->select('id', 'first_name', 'last_name', 'admission_number', 'gender_id', 'rank_id')
                          ->with(['gender' => function($q) {
                              $q->select('id', 'name');
                          }])
                          ->orderBy('first_name');
                }
            ])
            ->find($rankId);

        if (!$rank) {
            return response()->json([
                'error' => 'Class not found.'
            ], 404);
        }

        return response()->json([
            'rank' => $rank,
            'students' => $rank->students
        ]);
    }
}