<?php

namespace App\Http\Controllers\Exams;

use Inertia\Inertia;
use App\Models\Student;
use App\Models\Employee;
use App\Models\EmployeeClass;
use App\Models\ExamSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ExamStudentController extends Controller
{
    public function dataTableEnrollStudents(Request $request)
    {
        $request->validate([
            'exam_id'  => 'required|exists:exams,id',
            'class_id' => 'required|exists:ranks,id',
            'teacher_id' => 'sometimes|exists:employees,id',
        ]);

        // Get teacher ID from request or authenticated user's employee
        $teacherId = $request->teacher_id;
        
        if (!$teacherId && Auth::check()) {
            $user = Auth::user();
            if ($user->employee) {
                $teacherId = $user->employee->id;
            }
        }

        // Check if teacher has access to this class
        if ($teacherId) {
            $hasAccess = EmployeeClass::where('employee_id', $teacherId)
                ->where('class_id', $request->class_id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'error' => 'You do not have access to this class',
                    'enrolled_student_ids' => [],
                    'enrolled_students' => [],
                ], 403);
            }
        }

        $enrolledIds = DB::table('exam_students')
            ->where('exam_id', $request->exam_id)
            ->where('class_id', $request->class_id)
            ->pluck('student_id')
            ->toArray();

        $enrolledStudents = Student::whereIn('id', $enrolledIds)
            ->get()
            ->map(function ($s) {
                $admNo = $s->admission_number;
                $name = $s->name ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''));
                return [
                    'id' => $s->id,
                    'adm_no' => $admNo,
                    'name' => $name,
                    'class_id' => $s->class_id,
                ];
            });

        return response()->json([
            'enrolled_student_ids' => $enrolledIds,
            'enrolled_students'    => $enrolledStudents,
        ]);
    }

    public function getTeacherClassesAndStudents(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'teacher_id' => 'required|exists:employees,id',
        ]);

        $teacherId = $request->teacher_id;
        $examId = $request->exam_id;

        // Get teacher's assigned classes
        $teacherClasses = EmployeeClass::with(['class', 'subject'])
            ->where('employee_id', $teacherId)
            ->get()
            ->groupBy('class_id')
            ->map(function ($classAssignments) use ($examId, $teacherId) {
                $class = $classAssignments->first()->class;
                
                // Get enrolled students for this class and exam
                $enrolledStudents = $this->getEnrolledStudents($examId, $class->id);
                
                // Get all students in this class
                $allStudents = Student::where('class_id', $class->id)
                    ->get()
                    ->map(function ($student) use ($enrolledStudents) {
                        return [
                            'id' => $student->id,
                            'adm_no' => $student->admission_number,
                            'name' => $student->name ?? trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                            'is_enrolled' => in_array($student->id, $enrolledStudents->pluck('id')->toArray()),
                        ];
                    });

                return [
                    'class' => [
                        'id' => $class->id,
                        'name' => $class->name,
                        'stream' => $class->stream,
                    ],
                    'subjects' => $classAssignments->whereNotNull('subject_id')->pluck('subject'),
                    'is_class_teacher' => $classAssignments->contains('is_class_teacher', true),
                    'students' => $allStudents,
                    'enrolled_students' => $enrolledStudents,
                    'submission_status' => $this->getSubmissionStatus($examId, $class->id, $teacherId),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'teacher_classes' => $teacherClasses,
        ]);
    }

    public function index()
    {
        return Inertia::render('Exam/ExamStudent/Index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_id'  => 'required|exists:exams,id',
            'class_id' => 'required|integer|exists:ranks,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'integer|exists:students,id',
            'teacher_id' => 'sometimes|exists:employees,id',
        ]);

        // Get the correct teacher ID
        $teacherId = $this->getValidTeacherId($validated['class_id'], $validated['teacher_id'] ?? null);

        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'No valid teacher found for this class or you do not have access to enroll students in this class'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Remove existing enrollments for this exam and class
            DB::table('exam_students')
                ->where('exam_id', $validated['exam_id'])
                ->where('class_id', $validated['class_id'])
                ->delete();

            // Insert new enrollments
            if (!empty($validated['student_ids'])) {
                $insert = collect($validated['student_ids'])->map(fn($id) => [
                    'exam_id'    => $validated['exam_id'],
                    'class_id'   => $validated['class_id'],
                    'student_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();

                DB::table('exam_students')->insert($insert);
            }

            // Update or create exam submission record with valid teacher ID
            $this->updateExamSubmission([
                'exam_id' => $validated['exam_id'],
                'class_id' => $validated['class_id'],
                'teacher_id' => $teacherId,
                'student_ids' => $validated['student_ids']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Students enrolled successfully',
                'enrolled_count' => count($validated['student_ids'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to enroll students: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkEnrollStudents(Request $request)
    {
        $validated = $request->validate([
            'enrollments' => 'required|array',
            'enrollments.*.exam_id' => 'required|exists:exams,id',
            'enrollments.*.class_id' => 'required|exists:ranks,id',
            'enrollments.*.student_ids' => 'required|array',
            'enrollments.*.student_ids.*' => 'integer|exists:students,id',
            'teacher_id' => 'required|exists:employees,id',
        ]);

        $teacherId = $validated['teacher_id'];
        $results = [];

        // Verify the teacher exists and has access
        $teacher = Employee::find($teacherId);
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid teacher ID provided'
            ], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($validated['enrollments'] as $enrollment) {
                // Check teacher access for each class
                $hasAccess = EmployeeClass::where('employee_id', $teacherId)
                    ->where('class_id', $enrollment['class_id'])
                    ->exists();

                if (!$hasAccess) {
                    $results[] = [
                        'exam_id' => $enrollment['exam_id'],
                        'class_id' => $enrollment['class_id'],
                        'success' => false,
                        'message' => 'No access to this class'
                    ];
                    continue;
                }

                // Remove existing enrollments
                DB::table('exam_students')
                    ->where('exam_id', $enrollment['exam_id'])
                    ->where('class_id', $enrollment['class_id'])
                    ->delete();

                // Insert new enrollments
                if (!empty($enrollment['student_ids'])) {
                    $insert = collect($enrollment['student_ids'])->map(fn($id) => [
                        'exam_id'    => $enrollment['exam_id'],
                        'class_id'   => $enrollment['class_id'],
                        'student_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->toArray();

                    DB::table('exam_students')->insert($insert);
                }

                // Update exam submission
                $this->updateExamSubmission($enrollment + ['teacher_id' => $teacherId]);

                $results[] = [
                    'exam_id' => $enrollment['exam_id'],
                    'class_id' => $enrollment['class_id'],
                    'success' => true,
                    'message' => 'Enrolled ' . count($enrollment['student_ids']) . ' students',
                    'enrolled_count' => count($enrollment['student_ids'])
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk enrollment completed',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Bulk enrollment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEnrollmentStatistics(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'teacher_id' => 'sometimes|exists:employees,id',
        ]);

        $examId = $request->exam_id;
        $teacherId = $request->teacher_id;

        $query = DB::table('exam_students')
            ->join('ranks', 'exam_students.class_id', '=', 'ranks.id')
            ->where('exam_students.exam_id', $examId);

        // Filter by teacher's classes if teacher_id provided
        if ($teacherId) {
            $teacherClassIds = EmployeeClass::where('employee_id', $teacherId)
                ->pluck('class_id')
                ->toArray();

            $query->whereIn('exam_students.class_id', $teacherClassIds);
        }

        $enrollmentStats = $query->select(
                'exam_students.class_id',
                'ranks.name as class_name',
                DB::raw('COUNT(exam_students.student_id) as enrolled_count')
            )
            ->groupBy('exam_students.class_id', 'ranks.name')
            ->get();

        // Get total students per class for percentage calculation
        $totalStudents = Student::when($teacherId, function ($query) use ($teacherClassIds) {
                return $query->whereIn('class_id', $teacherClassIds);
            })
            ->select('class_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('class_id')
            ->pluck('total_count', 'class_id');

        $statistics = $enrollmentStats->map(function ($stat) use ($totalStudents) {
            $total = $totalStudents[$stat->class_id] ?? 0;
            return [
                'class_id' => $stat->class_id,
                'class_name' => $stat->class_name,
                'enrolled_count' => $stat->enrolled_count,
                'total_count' => $total,
                'enrollment_percentage' => $total > 0 ? round(($stat->enrolled_count / $total) * 100, 2) : 0,
            ];
        });

        $overallStats = [
            'total_classes' => $statistics->count(),
            'total_enrolled' => $statistics->sum('enrolled_count'),
            'total_students' => $statistics->sum('total_count'),
            'overall_percentage' => $statistics->sum('total_count') > 0 ? 
                round(($statistics->sum('enrolled_count') / $statistics->sum('total_count')) * 100, 2) : 0,
        ];

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'overall' => $overallStats,
        ]);
    }

    public function getTeacherEnrollmentOverview(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:employees,id',
        ]);

        $teacherId = $request->teacher_id;

        // Verify teacher exists
        $teacher = Employee::find($teacherId);
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found'
            ], 404);
        }

        // Get teacher's classes
        $teacherClasses = EmployeeClass::with('class')
            ->where('employee_id', $teacherId)
            ->get()
            ->groupBy('class_id');

        $overview = [];

        foreach ($teacherClasses as $classId => $assignments) {
            $class = $assignments->first()->class;
            
            // Get exams for this class
            $exams = DB::table('exam_classes')
                ->join('exams', 'exam_classes.exam_id', '=', 'exams.id')
                ->where('exam_classes.class_id', $classId)
                ->select('exams.id', 'exams.name', 'exams.exam_date')
                ->get();

            $classOverview = [
                'class_id' => $classId,
                'class_name' => $class->name,
                'stream' => $class->stream,
                'is_class_teacher' => $assignments->contains('is_class_teacher', true),
                'subjects' => $assignments->whereNotNull('subject_id')->pluck('subject'),
                'exams' => $exams->map(function ($exam) use ($classId) {
                    $enrolledCount = DB::table('exam_students')
                        ->where('exam_id', $exam->id)
                        ->where('class_id', $classId)
                        ->count();
                    
                    $totalStudents = Student::where('class_id', $classId)->count();

                    return [
                        'exam_id' => $exam->id,
                        'exam_name' => $exam->name,
                        'exam_date' => $exam->exam_date,
                        'enrolled_count' => $enrolledCount,
                        'total_students' => $totalStudents,
                        'enrollment_percentage' => $totalStudents > 0 ? round(($enrolledCount / $totalStudents) * 100, 2) : 0,
                    ];
                }),
            ];

            $overview[] = $classOverview;
        }

        return response()->json([
            'success' => true,
            'overview' => $overview,
        ]);
    }

    /**
     * Get enrolled students for exam and class
     */
    private function getEnrolledStudents($examId, $classId)
    {
        $enrolledIds = DB::table('exam_students')
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->pluck('student_id')
            ->toArray();

        return Student::whereIn('id', $enrolledIds)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'adm_no' => $student->admission_number,
                    'name' => $student->name ?? trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'class_id' => $student->class_id,
                ];
            });
    }

    /**
     * Get submission status for exam, class, and teacher
     */
    private function getSubmissionStatus($examId, $classId, $teacherId)
    {
        $submission = ExamSubmission::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$submission) {
            return [
                'exists' => false,
                'status' => 'not_started',
                'completion_percentage' => 0,
                'marks_entered_count' => 0,
                'total_marks_count' => 0,
            ];
        }

        return [
            'exists' => true,
            'status' => $submission->status,
            'completion_percentage' => $submission->completion_percentage,
            'marks_entered_count' => $submission->marks_entered_count,
            'total_marks_count' => $submission->total_marks_count,
            'submitted_at' => $submission->submitted_at,
            'approved_at' => $submission->approved_at,
        ];
    }

    /**
     * Get valid teacher ID for the class
     */
    private function getValidTeacherId($classId, $requestedTeacherId = null)
    {
        // If teacher ID is provided and valid, use it
        if ($requestedTeacherId) {
            $teacher = Employee::find($requestedTeacherId);
            if ($teacher) {
                // Check if this teacher has access to the class
                $hasAccess = EmployeeClass::where('employee_id', $requestedTeacherId)
                    ->where('class_id', $classId)
                    ->exists();
                
                if ($hasAccess) {
                    return $requestedTeacherId;
                }
            }
        }

        // If no valid teacher ID provided, try to get from authenticated user
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->employee) {
                $employeeId = $user->employee->id;
                
                // Check if this employee has access to the class
                $hasAccess = EmployeeClass::where('employee_id', $employeeId)
                    ->where('class_id', $classId)
                    ->exists();
                
                if ($hasAccess) {
                    return $employeeId;
                }
            }
        }

        // If still no valid teacher, get any teacher assigned to this class
        $classTeacher = EmployeeClass::where('class_id', $classId)
            ->where('is_class_teacher', true)
            ->first();

        if ($classTeacher) {
            return $classTeacher->employee_id;
        }

        // Get any teacher assigned to this class
        $anyTeacher = EmployeeClass::where('class_id', $classId)
            ->first();

        return $anyTeacher ? $anyTeacher->employee_id : null;
    }

    /**
     * Update or create exam submission record
     */
    private function updateExamSubmission($data)
    {
        $examId = $data['exam_id'];
        $classId = $data['class_id'];
        $teacherId = $data['teacher_id'];

        // Verify teacher exists
        $teacher = Employee::find($teacherId);
        if (!$teacher) {
            throw new \Exception("Invalid teacher ID: $teacherId");
        }

        $studentsCount = count($data['student_ids']);
        
        // Get subjects count from teacher's assignment
        $subjectsCount = EmployeeClass::where('employee_id', $teacherId)
            ->where('class_id', $classId)
            ->whereNotNull('subject_id')
            ->count();

        if ($subjectsCount === 0) {
            // Fallback to exam subjects count
            $subjectsCount = DB::table('exam_subjects')
                ->where('exam_id', $examId)
                ->where('class_id', $classId)
                ->count();
        }

        $totalMarksCount = $studentsCount * $subjectsCount;

        // For enrollment, submitted_by should be the current user
        $submittedBy = Auth::id();
        
        // Set submitted_at to current timestamp for initial creation
        $submittedAt = now();

        // Use the table's default status which is 'submitted'
        $status = 'submitted';

        ExamSubmission::updateOrCreate(
            [
                'exam_id' => $examId,
                'class_id' => $classId,
                'teacher_id' => $teacherId,
            ],
            [
                'submitted_by' => $submittedBy,
                'submitted_at' => $submittedAt,
                'students_count' => $studentsCount,
                'subjects_count' => $subjectsCount,
                'total_marks_count' => $totalMarksCount,
                'marks_entered_count' => 0,
                'completion_percentage' => 0,
                'status' => $status,
            ]
        );
    }
}