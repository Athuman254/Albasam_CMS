<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use App\Models\Student;
use App\Models\EmployeeClass;
use App\Models\Settings\AcademicYear;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\StudentPromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PromotionController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user();
        
        // Get classes where this teacher is a CLASS TEACHER
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

        $ranks = Rank::whereIn('id', $classTeacherRankIds)
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

        // Get all available classes for promotion (next classes)
        $allClasses = Rank::where('activated', true)
            ->select('id', 'name', 'stream_id', 'division_id')
            ->with([
                'stream' => function($q) {
                    $q->select('id', 'name');
                },
                'division' => function($q) {
                    $q->select('id', 'name');
                }
            ])
            ->orderBy('name')
            ->get();

        $isClassTeacher = $classTeacherAssignments->isNotEmpty();

        if (!$isClassTeacher) {
            return Inertia::render("Admin/Employees/Promotion/Index", [
                "ranks" => [],
                "allClasses" => $allClasses,
                "isClassTeacher" => false,
                "error" => "You are not a class teacher, you cannot promote students. Once a class is assigned to you, you can manage student promotions for your class."
            ]);
        }

        return Inertia::render("Admin/Employees/Promotion/Index", [
            "ranks" => $ranks,
            "allClasses" => $allClasses,
            "isClassTeacher" => true,
            "classTeacherAssignments" => $classTeacherAssignments
        ]);
    }

    public function getPromotionStats()
    {
        $employee = Auth::guard('employee')->user();
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return response()->json(['error' => 'No active academic year found'], 404);
        }

        // Get classes where teacher is class teacher
        $classTeacherAssignments = EmployeeClass::where('employee_id', $employee->id)
            ->where('is_class_teacher', true)
            ->pluck('class_id');

        if ($classTeacherAssignments->isEmpty()) {
            return response()->json(['error' => 'You are not a class teacher'], 403);
        }

        $stats = [
            'total_students' => Student::whereIn('rank_id', $classTeacherAssignments)->count(),
            'promoted_this_year' => StudentPromotion::whereIn('from_class_id', $classTeacherAssignments)
                ->where('academic_year_id', $currentAcademicYear->id)
                ->count(),
            'pending_promotion' => Student::whereIn('rank_id', $classTeacherAssignments)
                ->notPromotedThisYear()
                ->count(),
            'special_promotions' => StudentPromotion::whereIn('from_class_id', $classTeacherAssignments)
                ->where('academic_year_id', $currentAcademicYear->id)
                ->where('special_promotion', true)
                ->count(),
        ];

        return response()->json($stats);
    }

    private function extractFormLevel($className)
    {
        // Extract form number from class name (e.g., "Form 1" -> 1, "Form 2" -> 2)
        preg_match('/Form\s*(\d+)/i', $className, $matches);
        return $matches ? (int)$matches[1] : 0;
    }

    public function getClassStudents(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:ranks,id'
        ]);

        $classId = $request->class_id;
        $employee = Auth::guard('employee')->user();

        // Verify the teacher is class teacher for this class
        $isClassTeacherForClass = EmployeeClass::where('employee_id', $employee->id)
            ->where('class_id', $classId)
            ->where('is_class_teacher', true)
            ->exists();

        if (!$isClassTeacherForClass) {
            return response()->json([
                'error' => 'You are not authorized to view students for this class.'
            ], 403);
        }

        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return response()->json([
                'error' => 'No active academic year found.'
            ], 404);
        }

        // Get current class info for frontend
        $currentClass = Rank::find($classId);
        $currentFormLevel = $this->extractFormLevel($currentClass->name);

        // Get available next classes (only higher forms)
        $availableNextClasses = Rank::where('activated', true)
            ->get()
            ->filter(function($class) use ($currentFormLevel) {
                $classFormLevel = $this->extractFormLevel($class->name);
                return $classFormLevel > $currentFormLevel;
            })
            ->map(function($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'stream' => $class->stream,
                    'division' => $class->division,
                    'form_level' => $this->extractFormLevel($class->name)
                ];
            })
            ->values();

        // Get students who are currently in this class and haven't been promoted this academic year
        $students = Student::where('rank_id', $classId)
            ->notPromotedThisYear()
            ->select('id', 'first_name', 'last_name', 'middle_name', 'admission_number', 'gender_id', 'rank_id')
            ->with(['gender' => function($q) {
                $q->select('id', 'name');
            }])
            ->orderBy('first_name')
            ->get();

        // Check academic progress for each student
        $studentsWithProgress = $students->map(function($student) use ($currentAcademicYear) {
            $academicProgress = $this->checkStudentAcademicProgress($student->id, $currentAcademicYear->id);
            
            return [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'full_name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'gender' => $student->gender->name ?? 'N/A',
                'has_completed_all_terms' => $academicProgress['has_completed_all_terms'],
                'completed_terms' => $academicProgress['completed_terms'],
                'is_eligible_for_promotion' => $academicProgress['is_eligible_for_promotion'],
                'selected' => false // Default not selected
            ];
        });

        return response()->json([
            'students' => $studentsWithProgress,
            'class_name' => $currentClass->name ?? 'Unknown Class',
            'current_form_level' => $currentFormLevel,
            'available_next_classes' => $availableNextClasses,
            'currentAcademicYear' => $currentAcademicYear,
            'student_count' => $students->count()
        ]);
    }

    private function checkStudentAcademicProgress($studentId, $academicYearId)
    {
        // Get all exams for the academic year grouped by term
        $terms = [1, 2, 3];
        $completedTerms = [];
        
        foreach ($terms as $term) {
            // Check if student has any exam marks for this term
            $hasTermMarks = ExamMark::whereHas('examSubject.exam', function($query) use ($academicYearId, $term) {
                    $query->where('academic_year_id', $academicYearId)
                          ->where('term', $term);
                })
                ->where('student_id', $studentId)
                ->exists();
            
            if ($hasTermMarks) {
                $completedTerms[] = $term;
            }
        }

        $hasCompletedAllTerms = count($completedTerms) === 3;
        
        // Student is eligible if they completed all terms OR at least one term (for special cases)
        $isEligible = $hasCompletedAllTerms || count($completedTerms) > 0;

        return [
            'has_completed_all_terms' => $hasCompletedAllTerms,
            'completed_terms' => $completedTerms,
            'is_eligible_for_promotion' => $isEligible,
            'terms_completed_count' => count($completedTerms)
        ];
    }

    public function getStudentAcademicProgress($studentId)
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        
        if (!$currentAcademicYear) {
            return response()->json([
                'error' => 'No active academic year found.'
            ], 404);
        }

        $progress = $this->checkStudentAcademicProgress($studentId, $currentAcademicYear->id);

        return response()->json($progress);
    }

    public function promoteStudents(Request $request)
    {
        $request->validate([
            'current_class_id' => 'required|exists:ranks,id',
            'next_class_id' => 'required|exists:ranks,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'special_promotion' => 'boolean',
            'reason' => 'nullable|string|max:500'
        ]);

        $employee = Auth::guard('employee')->user();
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return response()->json([
                'error' => 'No active academic year found.'
            ], 404);
        }

        // Verify the teacher is class teacher for the current class
        $isClassTeacherForClass = EmployeeClass::where('employee_id', $employee->id)
            ->where('class_id', $request->current_class_id)
            ->where('is_class_teacher', true)
            ->exists();

        if (!$isClassTeacherForClass) {
            return response()->json([
                'error' => 'You are not authorized to promote students from this class.'
            ], 403);
        }

        // Get current and next class info
        $currentClass = Rank::find($request->current_class_id);
        $nextClass = Rank::find($request->next_class_id);
        
        // Validate promotion path using form levels
        $currentFormLevel = $this->extractFormLevel($currentClass->name);
        $nextFormLevel = $this->extractFormLevel($nextClass->name);
        
        // Prevent promotion to same or lower classes
        if ($nextFormLevel <= $currentFormLevel) {
            return response()->json([
                'error' => 'Invalid promotion path. Students can only be promoted to higher classes.'
            ], 422);
        }

        // Prevent skipping classes (only allow next immediate form level)
        if ($nextFormLevel !== $currentFormLevel + 1) {
            return response()->json([
                'error' => 'Invalid promotion path. Students can only be promoted to the next immediate class level.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $promotedCount = 0;
            $notEligibleCount = 0;
            $alreadyPromotedCount = 0;
            $notInClassCount = 0;

            foreach ($request->student_ids as $studentId) {
                $student = Student::find($studentId);
                
                // Check if student is already in the current class
                if ($student->rank_id != $request->current_class_id) {
                    $notInClassCount++;
                    continue;
                }

                // Check if student was already promoted this academic year
                $alreadyPromoted = StudentPromotion::where('student_id', $studentId)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->exists();

                if ($alreadyPromoted) {
                    $alreadyPromotedCount++;
                    continue;
                }

                // Check academic progress
                $progress = $this->checkStudentAcademicProgress($studentId, $currentAcademicYear->id);
                
                // Allow promotion if special promotion is enabled or student is eligible
                if ($request->special_promotion || $progress['is_eligible_for_promotion']) {
                    // Create promotion record
                    StudentPromotion::create([
                        'student_id' => $studentId,
                        'from_class_id' => $request->current_class_id,
                        'to_class_id' => $request->next_class_id,
                        'academic_year_id' => $currentAcademicYear->id,
                        'promoted_by' => $employee->id,
                        'special_promotion' => $request->special_promotion,
                        'reason' => $request->special_promotion ? $request->reason : null,
                        'has_completed_all_terms' => $progress['has_completed_all_terms'],
                        'completed_terms' => json_encode($progress['completed_terms']),
                        'promoted_at' => now()
                    ]);

                    // Update student's current class (rank_id)
                    $student->rank_id = $request->next_class_id;
                    $student->save();

                    $promotedCount++;
                } else {
                    $notEligibleCount++;
                }
            }

            DB::commit();

            $message = "Successfully promoted {$promotedCount} students.";
            $details = [];

            if ($notEligibleCount > 0) {
                $details[] = "{$notEligibleCount} students were not eligible for promotion";
            }
            if ($alreadyPromotedCount > 0) {
                $details[] = "{$alreadyPromotedCount} students were already promoted this academic year";
            }
            if ($notInClassCount > 0) {
                $details[] = "{$notInClassCount} students are not in the selected class";
            }

            if (!empty($details)) {
                $message .= " " . implode(', ', $details) . ".";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'promoted_count' => $promotedCount,
                'not_eligible_count' => $notEligibleCount,
                'already_promoted_count' => $alreadyPromotedCount,
                'not_in_class_count' => $notInClassCount,
                'from_class' => $currentClass->name,
                'to_class' => $nextClass->name,
                'from_form_level' => $currentFormLevel,
                'to_form_level' => $nextFormLevel
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Failed to promote students: ' . $e->getMessage()
            ], 500);
        }
    }

    private function isValidPromotionPath($currentClass, $nextClass)
    {
        // Extract form numbers from class names (e.g., "Form 1" -> 1, "Form 2" -> 2)
        $currentFormLevel = $this->extractFormLevel($currentClass);
        $nextFormLevel = $this->extractFormLevel($nextClass);
        
        if ($currentFormLevel === 0 || $nextFormLevel === 0) {
            // If not Form classes, allow any promotion (for non-standard classes)
            return true;
        }

        // Only allow promotion to the next immediate form (Form 1 -> Form 2, Form 2 -> Form 3, etc.)
        return $nextFormLevel === $currentFormLevel + 1;
    }

    /**
     * Get promotion history for a class
     */
    public function promotionHistory(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:ranks,id'
        ]);

        $classId = $request->class_id;
        $employee = Auth::guard('employee')->user();

        // Verify the teacher is class teacher for this class
        $isClassTeacherForClass = EmployeeClass::where('employee_id', $employee->id)
            ->where('class_id', $classId)
            ->where('is_class_teacher', true)
            ->exists();

        if (!$isClassTeacherForClass) {
            return response()->json([
                'error' => 'You are not authorized to view promotion history for this class.'
            ], 403);
        }

        $promotions = StudentPromotion::with([
                'student' => function($query) {
                    $query->select('id', 'first_name', 'last_name', 'admission_number');
                },
                'toClass' => function($query) {
                    $query->select('id', 'name');
                },
                'academicYear' => function($query) {
                    $query->select('id', 'name');
                },
                'promotedBy' => function($query) {
                    $query->select('id', 'first_name', 'last_name');
                }
            ])
            ->where('from_class_id', $classId)
            ->orderBy('promoted_at', 'desc')
            ->get()
            ->map(function($promotion) {
                return [
                    'id' => $promotion->id,
                    'student_name' => $promotion->student->full_name ?? 'N/A',
                    'admission_number' => $promotion->student->admission_number ?? 'N/A',
                    'to_class' => $promotion->toClass->name ?? 'N/A',
                    'academic_year' => $promotion->academicYear->name ?? 'N/A',
                    'promoted_by' => $promotion->promotedBy->full_name ?? 'N/A',
                    'promoted_at' => $promotion->promoted_at->format('Y-m-d H:i'),
                    'special_promotion' => $promotion->special_promotion,
                    'reason' => $promotion->reason,
                    'has_completed_all_terms' => $promotion->has_completed_all_terms,
                    'completed_terms' => $promotion->completed_terms ? json_decode($promotion->completed_terms) : []
                ];
            });

        return response()->json([
            'promotions' => $promotions
        ]);
    }

    /**
     * Get available next classes for a given class
     */
    public function getAvailableNextClasses(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:ranks,id'
        ]);

        $classId = $request->class_id;
        $currentClass = Rank::find($classId);
        $currentFormLevel = $this->extractFormLevel($currentClass->name);

        // Get available next classes (only higher forms)
        $availableNextClasses = Rank::where('activated', true)
            ->get()
            ->filter(function($class) use ($currentFormLevel) {
                $classFormLevel = $this->extractFormLevel($class->name);
                return $classFormLevel > $currentFormLevel;
            })
            ->map(function($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'stream' => $class->stream,
                    'division' => $class->division,
                    'form_level' => $this->extractFormLevel($class->name)
                ];
            })
            ->values();

        return response()->json([
            'current_class' => $currentClass->name,
            'current_form_level' => $currentFormLevel,
            'available_next_classes' => $availableNextClasses
        ]);
    }
}