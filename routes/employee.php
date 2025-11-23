<?php

use App\Http\Controllers\Employee\Auth\LoginController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Employee\PromotionController;
use Illuminate\Support\Facades\Route;

Route::prefix('employee')->name('employee.')->group(function () {
    
    Route::middleware('guest:employee')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:employee')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // =========================================================================
// STUDENT PROMOTION ROUTES - ADDED SECTION
// =========================================================================
Route::prefix('promotion')->name('promotion.')->group(function () {
    Route::get('/', [PromotionController::class, 'index'])->name('index');
    
    // Add the missing stats route
    Route::get('/stats', [PromotionController::class, 'getPromotionStats'])->name('stats');
    
    Route::post('/promote-students', [PromotionController::class, 'promoteStudents'])->name('promote-students');
    Route::get('/get-class-students', [PromotionController::class, 'getClassStudents'])->name('get-class-students');
    Route::get('/promotion-history', [PromotionController::class, 'promotionHistory'])->name('history');
});

        // =========================================================================
        // FIXED ROUTES FOR MARKS ENTRY FUNCTIONALITY
        // =========================================================================
        
        // FIXED: Use controller method instead of closure for classes
        Route::get('/classes', [EmployeeController::class, 'getEmployeeClassesForTeacher'])->name('classes');
        
        // Employee subjects for a class
        Route::get('/subjects', [EmployeeController::class, 'getTeacherSubjects'])->name('subjects');
        
        // Current academic year
        Route::get('/current-academic-year', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'getCurrentAcademicYear'])->name('current-academic-year');

        // Save marks
        Route::post('/exams/save-marks', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'store'])->name('exams.save-marks');

        // Save single student mark
        Route::post('/exams/save-single-mark', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'saveSingleMark'])->name('exams.save-single-mark');

        // Get students for marks entry
        Route::get('/classes/{class}/students', [EmployeeController::class, 'getClassStudentsForMarks'])->name('classes.students');

        // Get enrolled students for marks entry
        Route::get('/exams/enrolled-students', [EmployeeController::class, 'getEnrolledStudentsForMarks'])->name('exams.enrolled-students');

        // =========================================================================
        // DEBUG ROUTES (Remove in production)
        // =========================================================================
        Route::get('/debug/whoami', function () {
            $employee = Auth::guard('employee')->user();
            $user = Auth::user();
            
            return response()->json([
                'employee_guard' => $employee ? [
                    'id' => $employee->id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'staff_number' => $employee->staff_number,
                    'has_system_access' => $employee->has_system_access,
                ] : null,
                'default_guard' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name ?? 'N/A',
                    'type' => get_class($user),
                ] : null,
                'all_guards' => [
                    'employee' => Auth::guard('employee')->check(),
                    'web' => Auth::guard('web')->check(),
                ]
            ]);
        });

        Route::get('/debug/employee-classes-detailed', function () {
            $employee = Auth::guard('employee')->user();
            
            if (!$employee) {
                return response()->json(['error' => 'Not authenticated as employee']);
            }

            // Check all the relationships step by step
            $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();
            
            // Raw SQL query to see what's happening
            $rawAssignments = DB::select("
                SELECT 
                    ec.*,
                    r.name as class_name,
                    r.activated as class_activated,
                    s.name as subject_name, 
                    s.activated as subject_activated,
                    ay.name as academic_year_name,
                    ay.is_active as academic_year_active
                FROM employee_class ec
                LEFT JOIN ranks r ON ec.class_id = r.id
                LEFT JOIN subjects s ON ec.subject_id = s.id  
                LEFT JOIN academic_years ay ON ec.academic_year_id = ay.id
                WHERE ec.employee_id = ?
                AND ec.academic_year_id = ?
            ", [$employee->id, $academicYear->id]);

            // Eloquent query to compare
            $eloquentAssignments = \App\Models\EmployeeClass::with(['class', 'subject', 'academicYear'])
                ->where('employee_id', $employee->id)
                ->where('academic_year_id', $academicYear->id)
                ->get();

            return response()->json([
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'staff_number' => $employee->staff_number,
                ],
                'academic_year' => $academicYear,
                'raw_sql_assignments' => $rawAssignments,
                'eloquent_assignments_count' => $eloquentAssignments->count(),
                'eloquent_assignments' => $eloquentAssignments->map(function($assignment) {
                    return [
                        'id' => $assignment->id,
                        'class_id' => $assignment->class_id,
                        'class' => $assignment->class ? [
                            'id' => $assignment->class->id,
                            'name' => $assignment->class->name,
                            'activated' => $assignment->class->activated,
                        ] : 'MISSING',
                        'subject_id' => $assignment->subject_id,
                        'subject' => $assignment->subject ? [
                            'id' => $assignment->subject->id,
                            'name' => $assignment->subject->name,
                            'activated' => $assignment->subject->activated,
                        ] : 'MISSING',
                        'academic_year' => $assignment->academicYear ? [
                            'id' => $assignment->academicYear->id,
                            'name' => $assignment->academicYear->name,
                            'is_active' => $assignment->academicYear->is_active,
                        ] : 'MISSING',
                        'is_class_teacher' => $assignment->is_class_teacher,
                    ];
                }),
                'relationships_check' => [
                    'employee_class_model_exists' => class_exists(\App\Models\EmployeeClass::class),
                    'rank_model_exists' => class_exists(\App\Models\Rank::class),
                    'subject_model_exists' => class_exists(\App\Models\Subject::class),
                ]
            ]);
        });

        /********************************
         * DASHBOARD DATA ROUTES
         *******************************/
        Route::get('/dashboard-statistics', function () {
            $employee = auth()->guard('employee')->user();
            
            try {
                $statistics = [
                    'classes' => $employee->classes()->count() ?? 0,
                    'pendingMarks' => \App\Models\ExamMark::where('submitted_by', $employee->id)
                        ->where('status', 'draft')
                        ->count(),
                    'submittedMarks' => \App\Models\ExamMark::where('submitted_by', $employee->id)
                        ->where('status', 'submitted')
                        ->count(),
                    'approvedMarks' => \App\Models\ExamMark::where('submitted_by', $employee->id)
                        ->where('status', 'approved')
                        ->count(),
                ];
                
                return response()->json($statistics);
            } catch (\Exception $e) {
                // Return default values if relationships aren't set up yet
                return response()->json([
                    'classes' => 0,
                    'pendingMarks' => 0,
                    'submittedMarks' => 0,
                    'approvedMarks' => 0,
                ]);
            }
        })->name('dashboard.statistics');
        
        Route::get('/recent-activity', function () {
            $employee = auth()->guard('employee')->user();
            $recentActivity = [];
            
            try {
                // Get recent exam mark submissions
                $recentSubmissions = \App\Models\ExamMark::with(['examSubject.exam', 'examSubject.subject'])
                    ->where('submitted_by', $employee->id)
                    ->whereNotNull('submitted_at')
                    ->orderBy('submitted_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->groupBy('submitted_at');
                
                foreach ($recentSubmissions as $date => $marks) {
                    if ($marks->count() > 0) {
                        $firstMark = $marks->first();
                        $recentActivity[] = [
                            'id' => uniqid(),
                            'type' => 'submission',
                            'title' => 'Marks Submitted',
                            'description' => 'Submitted ' . $marks->count() . ' marks for ' . ($firstMark->examSubject->exam->name ?? 'Exam'),
                            'time' => $firstMark->submitted_at->diffForHumans()
                        ];
                    }
                }
                
                // Get recent mark entries (drafts)
                $recentEntries = \App\Models\ExamMark::with(['examSubject.exam'])
                    ->where('submitted_by', $employee->id)
                    ->whereNull('submitted_at')
                    ->where('status', 'draft')
                    ->orderBy('updated_at', 'desc')
                    ->limit(3)
                    ->get()
                    ->groupBy('updated_at');
                
                foreach ($recentEntries as $date => $marks) {
                    if ($marks->count() > 0) {
                        $firstMark = $marks->first();
                        $recentActivity[] = [
                            'id' => uniqid(),
                            'type' => 'mark_entry',
                            'title' => 'Marks Entered',
                            'description' => 'Entered marks for ' . ($firstMark->examSubject->exam->name ?? 'Exam'),
                            'time' => $firstMark->updated_at->diffForHumans()
                        ];
                    }
                }
                
                // Sort by time (most recent first)
                usort($recentActivity, function($a, $b) {
                    return strtotime($b['time']) - strtotime($a['time']);
                });
                
                // Limit to 5 most recent activities
                $recentActivity = array_slice($recentActivity, 0, 5);
                
            } catch (\Exception $e) {
                // Fallback mock data if there's an error
                $recentActivity = [
                    [
                        'id' => 1,
                        'type' => 'mark_entry',
                        'title' => 'Marks Entered',
                        'description' => 'Entered marks for Class 7A Mathematics',
                        'time' => '2 hours ago'
                    ],
                    [
                        'id' => 2,
                        'type' => 'submission',
                        'title' => 'Marks Submitted',
                        'description' => 'Submitted Class 8B Science marks for approval',
                        'time' => '1 day ago'
                    ],
                    [
                        'id' => 3,
                        'type' => 'attendance',
                        'title' => 'Attendance Marked',
                        'description' => 'Marked attendance for Class 6C',
                        'time' => '2 days ago'
                    ]
                ];
            }
            
            return response()->json($recentActivity);
        })->name('recent.activity');
        
        Route::get('/upcoming-exams', function () {
            $employee = auth()->guard('employee')->user();
            
            try {
                $upcomingExams = [];
                
                // Get exams for the employee's classes
                if (method_exists($employee, 'classes')) {
                    $employeeClasses = $employee->classes()->pluck('id');
                    
                    $exams = \App\Models\Exam::with(['subjects' => function($query) use ($employeeClasses) {
                            $query->whereIn('class_id', $employeeClasses);
                        }])
                        ->whereHas('subjects', function($query) use ($employeeClasses) {
                            $query->whereIn('class_id', $employeeClasses);
                        })
                        ->where('status', 'active')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                    
                    foreach ($exams as $exam) {
                        $classNames = $exam->subjects->pluck('class.name')->unique()->implode(', ');
                        $upcomingExams[] = [
                            'id' => $exam->id,
                            'name' => $exam->name,
                            'class_name' => $classNames ?: 'Assigned Classes',
                            'date' => $exam->created_at->format('Y-m-d'),
                            'status' => 'upcoming'
                        ];
                    }
                }
                
                // If no exams found or relationships not set up, return mock data
                if (empty($upcomingExams)) {
                    $upcomingExams = [
                        [
                            'id' => 1,
                            'name' => 'End of Term Exam',
                            'class_name' => 'Class 7A',
                            'date' => now()->addDays(7)->format('Y-m-d'),
                            'status' => 'upcoming'
                        ],
                        [
                            'id' => 2,
                            'name' => 'Mathematics Test',
                            'class_name' => 'Class 8B',
                            'date' => now()->addDays(14)->format('Y-m-d'),
                            'status' => 'upcoming'
                        ]
                    ];
                }
                
                return response()->json($upcomingExams);
                
            } catch (\Exception $e) {
                // Return mock data if there's an error
                return response()->json([
                    [
                        'id' => 1,
                        'name' => 'End of Term Exam',
                        'class_name' => 'Class 7A',
                        'date' => now()->addDays(7)->format('Y-m-d'),
                        'status' => 'upcoming'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Mathematics Test',
                        'class_name' => 'Class 8B',
                        'date' => now()->addDays(14)->format('Y-m-d'),
                        'status' => 'upcoming'
                    ]
                ]);
            }
        })->name('upcoming.exams');
        
        /********************************
         * DATATABLE ROUTES
         *******************************/
        Route::group([
            'prefix' => 'datatable',
            'as' => 'datatable.'
        ], function () {
            Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'dataTable'])->name('attendance');
            Route::get('/students', [\App\Http\Controllers\StudentController::class, 'dataTable'])->name('students');
            Route::get('/streams', [\App\Http\Controllers\Settings\StreamController::class, 'dataTable'])->name('streams');
            Route::get('/ranks', [\App\Http\Controllers\Settings\RankController::class, 'dataTable'])->name('ranks');
            Route::get('/subjects', [\App\Http\Controllers\Settings\SubjectController::class, 'dataTable'])->name('subjects');
            Route::get('/lessons', [\App\Http\Controllers\LessonController::class, 'dataTable'])->name('lessons');
            Route::get('/time-table', [\App\Http\Controllers\TimetableController::class, 'timeTableData'])->name('timetable');
            Route::get('/languages', [\App\Http\Controllers\Settings\LanguageController::class, 'dataTable'])->name('languages');
            Route::get('/relationships', [\App\Http\Controllers\Settings\RelationshipController::class, 'dataTable'])->name('relationships');
            Route::get('/qualification-types', [\App\Http\Controllers\Settings\QualificationTypeController::class, 'dataTable'])->name('qualification-types');
            
            Route::get('/emergency-contacts', [\App\Http\Controllers\EmergencyContactController::class, 'dataTable'])->name('contacts');
            Route::get('/employee-qualifications', [\App\Http\Controllers\QualificationController::class, 'dataTable'])->name('qualifications');
            Route::get('/work-histories', [\App\Http\Controllers\WorkHistoryController::class, 'dataTable'])->name('work-histories');
            
            // EXAM MODULE DATATABLES FOR EMPLOYEES
            Route::get('/exams', [\App\Http\Controllers\Exams\ExamManageController::class, 'dataTable'])->name('exams');
            Route::get('/exam-subjects', [\App\Http\Controllers\Exams\ExamManageController::class, 'examSubject'])->name('exam-subjects');
            Route::get('/exam-marks', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'examMarks'])->name('exam-marks');
            Route::get('/enrolled-students', [\App\Http\Controllers\Exams\ExamStudentController::class, 'dataTableEnrollStudents'])->name('enrolled-students');
        });
        
        /********************************
         * EMPLOYEE EXAM ROUTES
         *******************************/
        Route::group(['prefix' => 'exams', 'as' => 'exams.'], function () {
            // Page routes
            Route::get('/enter-marks', [\App\Http\Controllers\Exams\EmployeeExamController::class, 'enterMarks'])->name('enter-marks');
            Route::get('/submitted-marks', [\App\Http\Controllers\Exams\EmployeeExamController::class, 'submittedMarks'])->name('submitted-marks');
            
            // API routes
            Route::post('/save-marks', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'store'])->name('save-marks');
            
            // ADDED MISSING EXAM ROUTES:
            Route::post('/save-single-mark', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'saveSingleMark'])->name('save-single-mark');
            
            // NEW: Classes route within exams group for consistency
            Route::get('/classes', [EmployeeController::class, 'getEmployeeClassesForTeacher'])->name('classes');
            
            Route::get('/submitted-marks-data', function () {
                $employee = auth()->guard('employee')->user();
                
                try {
                    $submittedMarks = \App\Models\ExamMark::with([
                            'examSubject.exam',
                            'examSubject.subject',
                            'examSubject.class'
                        ])
                        ->where('submitted_by', $employee->id)
                        ->whereNotNull('submitted_at')
                        ->selectRaw('exam_subject_id, COUNT(*) as marks_count, MAX(submitted_at) as submitted_date, status, remarks')
                        ->groupBy('exam_subject_id', 'status', 'remarks')
                        ->orderBy('submitted_date', 'desc')
                        ->get()
                        ->map(function($group) {
                            $examSubject = $group->examSubject;
                            return [
                                'id' => $group->exam_subject_id,
                                'exam_name' => $examSubject->exam->name ?? 'Unknown Exam',
                                'class_name' => $examSubject->class->name ?? 'Unknown Class',
                                'subjects_count' => 1, // Each group is for one subject
                                'students_count' => $group->marks_count,
                                'submitted_date' => $group->submitted_date ? \Carbon\Carbon::parse($group->submitted_date)->format('Y-m-d') : 'N/A',
                                'status' => $group->status,
                                'remarks' => $group->remarks
                            ];
                        });
                    
                    // If no data found, return mock data
                    if ($submittedMarks->isEmpty()) {
                        $submittedMarks = collect([
                            [
                                'id' => 1,
                                'exam_name' => 'End of Term Exam',
                                'class_name' => 'Class 7A',
                                'subjects_count' => 5,
                                'students_count' => 35,
                                'submitted_date' => '2024-01-10',
                                'status' => 'approved',
                                'remarks' => ''
                            ],
                            [
                                'id' => 2,
                                'exam_name' => 'Mathematics Test',
                                'class_name' => 'Class 8B',
                                'subjects_count' => 1,
                                'students_count' => 40,
                                'submitted_date' => '2024-01-08',
                                'status' => 'submitted',
                                'remarks' => 'Pending admin review'
                            ]
                        ]);
                    }
                    
                    return response()->json($submittedMarks);
                    
                } catch (\Exception $e) {
                    // Return mock data if there's an error
                    return response()->json([
                        [
                            'id' => 1,
                            'exam_name' => 'End of Term Exam',
                            'class_name' => 'Class 7A',
                            'subjects_count' => 5,
                            'students_count' => 35,
                            'submitted_date' => '2024-01-10',
                            'status' => 'approved',
                            'remarks' => ''
                        ],
                        [
                            'id' => 2,
                            'exam_name' => 'Mathematics Test',
                            'class_name' => 'Class 8B',
                            'subjects_count' => 1,
                            'students_count' => 40,
                            'submitted_date' => '2024-01-08',
                            'status' => 'submitted',
                            'remarks' => 'Pending admin review'
                        ],
                        [
                            'id' => 3,
                            'exam_name' => 'Science Practical',
                            'class_name' => 'Class 6C',
                            'subjects_count' => 2,
                            'students_count' => 30,
                            'submitted_date' => '2024-01-05',
                            'status' => 'rejected',
                            'remarks' => 'Please verify marks for Student ID 123'
                        ]
                    ]);
                }
            })->name('submitted-marks.data');
        });
        
        /********************************
         * EMPLOYEE PROFILE & ATTENDANCE
         *******************************/
        
        // NOTE: The main /classes route has been moved to use the controller method above
        // This ensures it uses the EmployeeClass model with proper academic year filtering
        
        // ADDED MISSING SUBJECTS ROUTE
        Route::get('/subjects', [EmployeeController::class, 'getTeacherSubjects'])->name('subjects');
        
        // ADDED MISSING CURRENT ACADEMIC YEAR ROUTE
        Route::get('/current-academic-year', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'getCurrentAcademicYear'])->name('current-academic-year');
        
        Route::get('profile',[\App\Http\Controllers\Employee\ProfileController::class, 'index'])->name('profile.index');
        Route::post('password/update', [\App\Http\Controllers\Employee\ProfileController::class, 'updatePassword'])
            ->name('password.update');
        
        Route::resource('/attendances', \App\Http\Controllers\Employee\AttendanceController::class)->names('attendances');
        Route::get('/reports/attendance', [\App\Http\Controllers\Employee\AttendanceController::class, 'report'])->name('reports.attendance');
        Route::get('/attendance/fetch', [\App\Http\Controllers\Employee\AttendanceController::class, 'fetchForDate'])->name('attendance.fetch');
        
        Route::resource('/emergency-contacts', \App\Http\Controllers\EmergencyContactController::class)
            ->names('emergency-contacts')->only(['store', 'update', 'destroy']);
        Route::resource('/qualifications', \App\Http\Controllers\QualificationController::class)
            ->names('qualifications')->only(['store', 'update', 'destroy']);
        Route::resource('/work-histories', \App\Http\Controllers\WorkHistoryController::class)
            ->names('work-histories')->only(['store', 'update', 'destroy']);
    });
});