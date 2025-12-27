<?php

namespace App\Http\Controllers\Exams;

use Inertia\Inertia;
use App\Models\ExamMark;
use App\Models\ExamSubject;
use App\Models\EmployeeClass;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Rank;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UploadExamResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Exam/UploadExamResult/Index');
    }

    /**
     * Get current academic year
     */
    public function getCurrentAcademicYear()
    {
        $currentAcademicYear = \App\Models\AcademicYear::where('is_active', true)->first();

        return response()->json([
            'success' => true,
            'id' => $currentAcademicYear?->id,
            'name' => $currentAcademicYear?->name,
            'year' => $currentAcademicYear
        ]);
    }

    /**
     * Get exam marks with filtering and pagination
     */
    public function examMarks()
    {
        $marks = QueryBuilder::for(
            ExamMark::with([
                'student',
                'examSubject.subject',
                'submittedBy',
                'approvedBy',
                'exam',
                'class'
            ])->orderBy('id', 'desc')
        )
            ->allowedFilters([
                AllowedFilter::exact('student_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('exam_id'),
                AllowedFilter::exact('class_id'),
                AllowedFilter::exact('subject_id'),
                AllowedFilter::exact('exam_subject_id'),
                AllowedFilter::callback('teacher_id', function ($query, $value) {
                    $query->where('submitted_by', $value);
                }),
                AllowedFilter::callback('academic_year_id', function ($query, $value) {
                    $query->whereHas('exam', function ($q) use ($value) {
                        $q->where('academic_year_id', $value);
                    });
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->whereHas('student', function ($q) use ($value) {
                        $q->where(function ($sq) use ($value) {
                            $sq->where('first_name', 'like', "%{$value}%")
                                ->orWhere('middle_name', 'like', "%{$value}%")
                                ->orWhere('last_name', 'like', "%{$value}%")
                                ->orWhere('admission_number', 'like', "%{$value}%");
                        });
                    });
                }),
            ])
            ->allowedSorts([
                'created_at',
                'updated_at',
                'marks_obtained',
                'student_id',
                'submitted_at'
            ])
            ->jsonPaginate();

        return Resource::collection($marks);
    }

    /**
     * Show pending marks for admin approval
     */
    public function approvalQueue(Request $request)
    {
        $pendingMarks = QueryBuilder::for(
            ExamMark::with([
                'student',
                'examSubject.subject',
                'examSubject.exam',
                'submittedBy',
                'class',
                'exam'
            ])
                ->submitted()
                ->orderBy('submitted_at', 'desc')
        )
            ->allowedFilters([
                AllowedFilter::exact('exam_id'),
                AllowedFilter::exact('class_id'),
                AllowedFilter::exact('subject_id'),
                AllowedFilter::exact('exam_subject_id'),
                AllowedFilter::callback('academic_year_id', function ($query, $value) {
                    $query->whereHas('exam', function ($q) use ($value) {
                        $q->where('academic_year_id', $value);
                    });
                }),
            ])
            ->jsonPaginate();

        return Resource::collection($pendingMarks);
    }

    /**
     * Store a newly created resource in storage.
     * Handles subject-specific marks entry
     * UPDATED: Now supports both frontend array format and backend object format
     * IMPROVED: Auto-creates ExamSubject if it doesn't exist
     */
    public function store(Request $request)
    {
        // 1. Data Transformation: Convert various frontend formats to a unified structure

        // Handle 'overall_marks' (EnterMarks.vue format)
        if ($request->has('overall_marks') && is_array($request->overall_marks)) {
            $convertedMarks = [];
            foreach ($request->overall_marks as $markData) {
                if (isset($markData['student_id']) && isset($markData['marks'])) {
                    $convertedMarks[(int)$markData['student_id']] = $markData['marks'];
                }
            }
            $request->merge(['marks' => $convertedMarks]);
        }

        // Handle legacy 'marks' array-of-objects format
        if ($request->has('marks') && is_array($request->marks) && isset($request->marks[0]) && is_array($request->marks[0])) {
            $convertedMarks = [];
            foreach ($request->marks as $markData) {
                if (isset($markData['student_id']) && isset($markData['marks'])) {
                    $convertedMarks[(int)$markData['student_id']] = $markData['marks'];
                }
            }
            $request->merge(['marks' => $convertedMarks]);
        }

        // FIX: Convert all numeric fields to proper types before validation
        $request->merge([
            'exam_id' => $request->has('exam_id') ? (int)$request->exam_id : null,
            'class_id' => $request->has('class_id') ? (int)$request->class_id : null,
            'subject_id' => $request->has('subject_id') ? (int)$request->subject_id : null,
        ]);

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|integer|exists:ranks,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required_without:skill_marks|array',
            'marks.*' => 'nullable|numeric|min:0',
            'skill_marks' => 'sometimes|array',
            'skill_marks.*.student_id' => 'required_with:skill_marks|exists:students,id',
            'skill_marks.*.exam_subject_skill_id' => 'required_with:skill_marks|exists:exam_subject_skills,id',
            'skill_marks.*.marks_obtained' => 'required_with:skill_marks|numeric|min:0',
            'submit_for_approval' => 'sometimes|boolean',
            'remarks' => 'nullable|string|max:500',
            'is_bulk_upload' => 'sometimes|boolean',
            'skill_mode' => 'sometimes|boolean',
        ]);

        $examId = $validated['exam_id'];
        $classId = $validated['class_id'];
        $subjectId = $validated['subject_id'];
        $marks = $validated['marks'] ?? [];
        $skillMarks = $validated['skill_marks'] ?? [];
        $submitForApproval = $validated['submit_for_approval'] ?? false;
        $remarks = $validated['remarks'] ?? null;
        $isBulkUpload = $validated['is_bulk_upload'] ?? false;
        $skillMode = $validated['skill_mode'] ?? false;

        $currentUser = Auth::guard('employee')->user() ?? Auth::user();
        $employeeId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->id : null;
        $userId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->user_id : ($currentUser ? $currentUser->id : null);
        $isAdmin = $currentUser && $currentUser->hasRole('admin');

        // Verify teacher has access to this subject in this class
        $hasAccess = false;
        if ($employeeId) {
            $hasAccess = EmployeeClass::where('employee_id', $employeeId)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->exists();
        }

        if (!$hasAccess && !$isAdmin) {
            Log::warning('Unauthorized mark upload attempt blocked', [
                'user_id' => $userId,
                'employee_id' => $employeeId,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'is_admin' => $isAdmin
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to upload marks for this class/subject.',
            ], 403);
        }

        // IMPROVEMENT: Load or create exam_subject for this exam + class + subject combination
        $examSubject = ExamSubject::with('subject')
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$examSubject) {
            // Create the exam subject if it doesn't exist
            try {
                $examSubject = ExamSubject::create([
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'max_marks' => 100, // Default maximum marks
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Reload with subject relationship
                $examSubject->load('subject');

                Log::info('Auto-created ExamSubject', [
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'exam_subject_id' => $examSubject->id,
                    'user_id' => $userId
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create ExamSubject', [
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create exam subject configuration: ' . $e->getMessage(),
                ], 500);
            }
        }

        $errors = [];
        $toSave = [];
        $maxMarks = $examSubject->max_marks;
        $now = now();

        // Process marks based on mode
        if ($skillMode && !empty($skillMarks)) {
            // Group skill marks by student
            $groupedSkillMarks = [];
            foreach ($skillMarks as $sm) {
                $groupedSkillMarks[$sm['student_id']][] = $sm;
            }

            foreach ($groupedSkillMarks as $studentId => $studentSkills) {
                $totalObtained = collect($studentSkills)->sum('marks_obtained');

                if ($totalObtained > $maxMarks) {
                    $errors["skill_marks.$studentId"] = ["Total marks obtained ({$totalObtained}) exceeds subject maximum ({$maxMarks})"];
                    continue;
                }

                $percentage = ($totalObtained / $maxMarks) * 100;
                $grade = $this->calculateGrade($percentage);
                $status = $submitForApproval ? ExamMark::SUBMITTED : ExamMark::DRAFT;

                $toSave[] = [
                    'exam_subject_id' => $examSubject->id,
                    'student_id' => $studentId,
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'marks_obtained' => $totalObtained,
                    'maximum_marks' => $maxMarks,
                    'percentage' => round($percentage, 2),
                    'grade' => $grade,
                    'status' => $status,
                    'remarks' => $remarks,
                    'teacher_id' => $employeeId,
                    'submitted_by' => $submitForApproval ? $userId : null,
                    'submitted_at' => $submitForApproval ? $now : null,
                    'approved_by' => null,
                    'approved_at' => null,
                ];
            }
        } else {
            // Overall marks mode
            foreach ($marks as $studentId => $score) {
                if ($score === '' || $score === null) {
                    // For bulk upload, we might want to delete existing marks if empty
                    if ($isBulkUpload) {
                        ExamMark::where('exam_subject_id', $examSubject->id)
                            ->where('student_id', $studentId)
                            ->where('exam_id', $examId)
                            ->where('class_id', $classId)
                            ->delete();
                    }
                    continue;
                }

                if (!is_numeric($score)) {
                    $errors["marks.$studentId"] = ["Mark must be a number."];
                    continue;
                }

                $score = (float) $score;

                if ($score < 0 || $score > $maxMarks) {
                    $errors["marks.$studentId"] = ["Marks must be between 0 and {$maxMarks}"];
                    continue;
                }

                $percentage = ($score / $maxMarks) * 100;
                $grade = $this->calculateGrade($percentage);
                $status = $submitForApproval ? ExamMark::SUBMITTED : ExamMark::DRAFT;

                $toSave[] = [
                    'exam_subject_id' => $examSubject->id,
                    'student_id' => $studentId,
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'marks_obtained' => $score,
                    'maximum_marks' => $maxMarks,
                    'percentage' => round($percentage, 2),
                    'grade' => $grade,
                    'status' => $status,
                    'remarks' => $remarks,
                    'teacher_id' => $employeeId,
                    'submitted_by' => $submitForApproval ? $userId : null,
                    'submitted_at' => $submitForApproval ? $now : null,
                    'approved_by' => null,
                    'approved_at' => null,
                ];
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Some marks were invalid',
                'errors' => $errors,
            ], 422);
        }

        DB::transaction(function () use ($toSave, $examId, $classId, $examSubject, $skillMode, $skillMarks) {
            foreach ($toSave as $row) {
                $examMark = ExamMark::updateOrCreate(
                    [
                        'exam_subject_id' => $row['exam_subject_id'],
                        'student_id' => $row['student_id'],
                        'exam_id' => $examId,
                        'class_id' => $classId,
                    ],
                    $row
                );

                // If in skill mode, save individual skill marks
                if ($skillMode && !empty($skillMarks)) {
                    foreach ($skillMarks as $sm) {
                        if ($sm['student_id'] == $row['student_id']) {
                            \App\Models\ExamSkillMark::updateOrCreate(
                                [
                                    'exam_mark_id' => $examMark->id,
                                    'exam_subject_skill_id' => $sm['exam_subject_skill_id'],
                                ],
                                [
                                    'marks_obtained' => $sm['marks_obtained'],
                                ]
                            );
                        }
                    }
                }
            }
        });

        $message = $submitForApproval ?
            'Marks saved and submitted for approval successfully!' :
            'Marks saved as draft successfully!';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'submitted_for_approval' => $submitForApproval,
                'students_updated' => count($toSave),
                'subject' => $examSubject->subject->name,
                'max_marks' => $maxMarks,
                'exam_subject_id' => $examSubject->id,
            ]
        ]);
    }

    /**
     * Save single student mark (for quick save functionality)
     * IMPROVED: Auto-creates ExamSubject if it doesn't exist
     */
    public function saveSingleMark(Request $request)
    {
        // FIX: Convert all numeric fields to proper types before validation
        $request->merge([
            'exam_id' => (int)$request->exam_id,
            'class_id' => (int)$request->class_id,
            'subject_id' => (int)$request->subject_id,
            'student_id' => (int)$request->student_id,
            'marks_obtained' => (float)$request->marks_obtained,
        ]);

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|integer|exists:ranks,id',
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required|exists:students,id',
            'marks_obtained' => 'required|numeric|min:0',
            'submit_for_approval' => 'sometimes|boolean',
            'remarks' => 'nullable|string|max:500',
        ]);

        $examId = $validated['exam_id'];
        $classId = $validated['class_id'];
        $subjectId = $validated['subject_id'];
        $studentId = $validated['student_id'];
        $marksObtained = $validated['marks_obtained'];
        $submitForApproval = $validated['submit_for_approval'] ?? false;
        $remarks = $validated['remarks'] ?? null;

        $currentUser = Auth::guard('employee')->user() ?? Auth::user();
        $employeeId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->id : null;
        $userId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->user_id : ($currentUser ? $currentUser->id : null);
        $isAdmin = $currentUser && $currentUser->hasRole('admin');

        // Verify teacher has access to this subject in this class
        $hasAccess = false;
        if ($employeeId) {
            $hasAccess = EmployeeClass::where('employee_id', $employeeId)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->exists();
        }

        if (!$hasAccess && !$isAdmin) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to upload marks for this class/subject.',
            ], 403);
        }

        // IMPROVEMENT: Load or create exam_subject
        $examSubject = ExamSubject::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$examSubject) {
            // Create the exam subject if it doesn't exist
            try {
                $examSubject = ExamSubject::create([
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'max_marks' => 100, // Default maximum marks
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info('Auto-created ExamSubject for single mark', [
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'exam_subject_id' => $examSubject->id,
                    'user_id' => $userId
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create ExamSubject for single mark', [
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create exam subject configuration: ' . $e->getMessage(),
                ], 500);
            }
        }

        $maxMarks = $examSubject->max_marks;

        // Validate mark against maximum
        if ($marksObtained < 0 || $marksObtained > $maxMarks) {
            return response()->json([
                'status' => 'error',
                'message' => "Mark must be between 0 and {$maxMarks}.",
            ], 422);
        }

        $status = $submitForApproval ? ExamMark::SUBMITTED : ExamMark::DRAFT;
        $percentage = ($marksObtained / $maxMarks) * 100;
        $grade = $this->calculateGrade($percentage);

        $examMark = ExamMark::updateOrCreate(
            [
                'exam_subject_id' => $examSubject->id,
                'student_id' => $studentId,
                'exam_id' => $examId,
                'class_id' => $classId,
            ],
            [
                'marks_obtained' => $marksObtained,
                'maximum_marks' => $maxMarks,
                'percentage' => round($percentage, 2),
                'grade' => $grade,
                'status' => $status,
                'remarks' => $remarks,
                'teacher_id' => $employeeId,
                'submitted_by' => $submitForApproval ? $userId : null,
                'submitted_at' => $submitForApproval ? now() : null,
                'approved_by' => null,
                'approved_at' => null,
            ]
        );

        $message = $submitForApproval ?
            'Mark submitted for approval successfully!' :
            'Mark saved as draft successfully!';

        // Add info about auto-created exam subject if it was created
        $additionalInfo = $examSubject->wasRecentlyCreated ? ' Exam subject configuration was automatically created.' : '';

        return response()->json([
            'status' => 'success',
            'message' => $message . $additionalInfo,
            'data' => [
                'id' => $examMark->id,
                'student_id' => $studentId,
                'marks_obtained' => $marksObtained,
                'percentage' => round($percentage, 2),
                'grade' => $grade,
                'status' => $status,
                'exam_subject_id' => $examSubject->id,
                'exam_subject_created' => $examSubject->wasRecentlyCreated
            ]
        ]);
    }

    /**
     * Submit existing draft marks for approval
     * IMPROVED: Auto-creates ExamSubject if it doesn't exist
     */
    public function submitForApproval(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|integer|exists:ranks,id',
            'subject_id' => 'required|exists:subjects,id',
            'student_ids' => 'sometimes|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $examId = $validated['exam_id'];
        $classId = $validated['class_id'];
        $subjectId = $validated['subject_id'];
        $studentIds = $validated['student_ids'] ?? null;

        // IMPROVEMENT: Load or create exam subject
        $examSubject = ExamSubject::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$examSubject) {
            // Create the exam subject if it doesn't exist
            try {
                $examSubject = ExamSubject::create([
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'max_marks' => 100,
                    'created_by' => $userId,
                ]);

                Log::info('Auto-created ExamSubject for bulk submission', [
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'exam_subject_id' => $examSubject->id,
                    'user_id' => $userId
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create exam subject configuration: ' . $e->getMessage(),
                ], 500);
            }
        }

        $query = ExamMark::where('exam_subject_id', $examSubject->id)
            ->where('status', ExamMark::DRAFT);

        if ($studentIds) {
            $query->whereIn('student_id', $studentIds);
        }

        $currentUser = Auth::guard('employee')->user() ?? Auth::user();
        $userId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->user_id : ($currentUser ? $currentUser->id : null);

        $updatedCount = $query->update([
            'status' => ExamMark::SUBMITTED,
            'submitted_by' => $userId,
            'submitted_at' => now(),
        ]);

        $additionalInfo = $examSubject->wasRecentlyCreated ? ' Exam subject configuration was automatically created.' : '';

        return response()->json([
            'status' => 'success',
            'message' => "{$updatedCount} marks submitted for approval successfully!" . $additionalInfo,
            'data' => [
                'marks_updated' => $updatedCount,
                'exam_subject_id' => $examSubject->id,
                'exam_subject_created' => $examSubject->wasRecentlyCreated
            ]
        ]);
    }

    /**
     * Approve submitted marks
     */
    public function approveMarks(Request $request)
    {
        $validated = $request->validate([
            'mark_ids' => 'required|array',
            'mark_ids.*' => 'exists:exam_marks,id',
            'remarks' => 'nullable|string|max:500',
        ]);

        $markIds = $validated['mark_ids'];
        $remarks = $validated['remarks'] ?? null;

        $currentUser = Auth::guard('employee')->user() ?? Auth::user();
        $userId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->user_id : ($currentUser ? $currentUser->id : null);

        $updatedCount = ExamMark::whereIn('id', $markIds)
            ->where('status', ExamMark::SUBMITTED)
            ->update([
                'status' => ExamMark::APPROVED,
                'approved_by' => $userId,
                'approved_at' => now(),
                'remarks' => $remarks ?: DB::raw('remarks'),
            ]);

        return response()->json([
            'status' => 'success',
            'message' => "{$updatedCount} marks approved successfully!",
            'data' => [
                'marks_approved' => $updatedCount
            ]
        ]);
    }

    /**
     * Reject submitted marks
     */
    public function rejectMarks(Request $request)
    {
        $validated = $request->validate([
            'mark_ids' => 'required|array',
            'mark_ids.*' => 'exists:exam_marks,id',
            'rejection_reason' => 'required|string|max:500',
        ]);

        $markIds = $validated['mark_ids'];
        $rejectionReason = $validated['rejection_reason'];

        $currentUser = Auth::guard('employee')->user() ?? Auth::user();
        $userId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->user_id : ($currentUser ? $currentUser->id : null);

        $updatedCount = ExamMark::whereIn('id', $markIds)
            ->where('status', ExamMark::SUBMITTED)
            ->update([
                'status' => ExamMark::REJECTED,
                'remarks' => $rejectionReason,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

        return response()->json([
            'status' => 'success',
            'message' => "{$updatedCount} marks rejected successfully!",
            'data' => [
                'marks_rejected' => $updatedCount
            ]
        ]);
    }

    /**
     * Return marks to teacher for revision
     */
    public function returnMarks(Request $request)
    {
        $validated = $request->validate([
            'mark_ids' => 'required|array',
            'mark_ids.*' => 'exists:exam_marks,id',
            'return_reason' => 'required|string|max:500',
        ]);

        $markIds = $validated['mark_ids'];
        $returnReason = $validated['return_reason'];

        $updatedCount = ExamMark::whereIn('id', $markIds)
            ->where('status', ExamMark::SUBMITTED)
            ->update([
                'status' => ExamMark::DRAFT,
                'remarks' => $returnReason,
                'submitted_by' => null,
                'submitted_at' => null,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => "{$updatedCount} marks returned for revision successfully!",
            'data' => [
                'marks_returned' => $updatedCount
            ]
        ]);
    }

    /**
     * Get teacher's assigned subjects for a class
     */
    public function getTeacherSubjects(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:ranks,id',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'exam_id' => 'sometimes|exists:exams,id',
        ]);

        $classId = $request->class_id;
        $academicYearId = $request->academic_year_id;
        $examId = $request->exam_id;

        $currentUser = Auth::guard('employee')->user() ?? Auth::user();
        $employeeId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->id : null;

        $subjects = EmployeeClass::with('subject')
            ->where('employee_id', $employeeId)
            ->where('class_id', $classId)
            ->whereNotNull('subject_id')
            ->when($academicYearId, function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            })
            ->get()
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->values();

        // If exam_id is provided, check which subjects have exam subjects configured
        if ($examId) {
            $subjects = $subjects->map(function ($subject) use ($examId, $classId) {
                $examSubject = ExamSubject::where('exam_id', $examId)
                    ->where('class_id', $classId)
                    ->where('subject_id', $subject->id)
                    ->first();

                $subject->exam_subject_exists = !is_null($examSubject);
                $subject->exam_subject_id = $examSubject ? $examSubject->id : null;
                $subject->max_marks = $examSubject ? $examSubject->max_marks : null;

                return $subject;
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $subjects
        ]);
    }

    /**
     * Delete exam marks
     */
    public function destroy(Request $request, $id = null)
    {
        if ($id) {
            // Single mark deletion
            $mark = ExamMark::findOrFail($id);

            $currentUser = Auth::guard('employee')->user() ?? Auth::user();
            $employeeId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->id : null;

            // Check if user has permission to delete
            if ($mark->teacher_id !== $employeeId && (!$currentUser || !$currentUser->hasRole('admin'))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to delete this mark.',
                ], 403);
            }

            $mark->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Mark deleted successfully!',
            ]);
        }

        // Bulk deletion
        $validated = $request->validate([
            'mark_ids' => 'required|array',
            'mark_ids.*' => 'exists:exam_marks,id',
        ]);

        $markIds = $validated['mark_ids'];

        // For bulk deletion, only allow if user is admin or owns all marks
        $currentUser = Auth::guard('employee')->user() ?? Auth::user();
        $employeeId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->id : null;

        if (!$currentUser || !$currentUser->hasRole('admin')) {
            $foreignMarks = ExamMark::whereIn('id', $markIds)
                ->where('teacher_id', '!=', $employeeId)
                ->exists();

            if ($foreignMarks) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only delete your own marks.',
                ], 403);
            }
        }

        $deletedCount = ExamMark::whereIn('id', $markIds)->delete();

        return response()->json([
            'status' => 'success',
            'message' => "{$deletedCount} marks deleted successfully!",
            'data' => [
                'marks_deleted' => $deletedCount
            ]
        ]);
    }

    /**
     * Get marks statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        $request->validate([
            'exam_id' => 'sometimes|exists:exams,id',
            'class_id' => 'sometimes|exists:ranks,id',
            'subject_id' => 'sometimes|exists:subjects,id',
        ]);

        $currentUser = Auth::guard('employee')->user() ?? Auth::user();
        $employeeId = ($currentUser instanceof \App\Models\Employee) ? $currentUser->id : null;
        $examId = $request->exam_id;
        $classId = $request->class_id;
        $subjectId = $request->subject_id;

        $query = ExamMark::where('teacher_id', $employeeId);

        if ($examId) {
            $query->where('exam_id', $examId);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($subjectId) {
            $examSubjectIds = ExamSubject::where('subject_id', $subjectId)
                ->when($examId, function ($q) use ($examId) {
                    $q->where('exam_id', $examId);
                })
                ->when($classId, function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                })
                ->pluck('id');

            $query->whereIn('exam_subject_id', $examSubjectIds);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_marks,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft_marks,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as submitted_marks,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_marks,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_marks
        ', [
            ExamMark::DRAFT,
            ExamMark::SUBMITTED,
            ExamMark::APPROVED,
            ExamMark::REJECTED
        ])->first();

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Calculate grade based on percentage
     */
    private function calculateGrade($percentage)
    {
        return \App\Services\GradingService::getGrade($percentage);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for API controller
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $mark = ExamMark::with([
            'student',
            'examSubject.subject',
            'submittedBy',
            'approvedBy',
            'exam',
            'class'
        ])->findOrFail($id);

        return new Resource($mark);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Not used for API controller
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'marks_obtained' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $mark = ExamMark::with('examSubject')->findOrFail($id);

        $currentUser = Auth::guard('employee')->user() ?? Auth::user();
        $currentUserId = Auth::guard('employee')->id() ?? Auth::id();

        // Check permissions
        if ($mark->teacher_id !== $currentUserId && (!$currentUser || !$currentUser->hasRole('admin'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to update this mark.',
            ], 403);
        }

        $maxMarks = $mark->examSubject->max_marks;
        $marksObtained = $validated['marks_obtained'];

        if ($marksObtained > $maxMarks) {
            return response()->json([
                'status' => 'error',
                'message' => "Mark cannot exceed maximum marks ({$maxMarks}).",
            ], 422);
        }

        $percentage = ($marksObtained / $maxMarks) * 100;
        $grade = $this->calculateGrade($percentage);

        $mark->update([
            'marks_obtained' => $marksObtained,
            'percentage' => round($percentage, 2),
            'grade' => $grade,
            'remarks' => $validated['remarks'] ?? $mark->remarks,
            'status' => ExamMark::DRAFT,
            'submitted_by' => null,
            'submitted_at' => null,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mark updated successfully!',
            'data' => new Resource($mark->fresh())
        ]);
    }

    /**
     * Admin-only method to update marks even after approval
     * Includes audit trail for accountability
     */
    public function adminUpdateMark(Request $request, $markId)
    {
        $currentUser = Auth::guard('employee')->user() ?? Auth::user();

        // Verify admin role
        if (!$currentUser || !$currentUser->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only administrators can edit approved marks.'
            ], 403);
        }

        $validated = $request->validate([
            'marks_obtained' => 'required|numeric|min:0',
            'edit_reason' => 'required|string|max:500',
        ]);

        $examMark = ExamMark::with(['examSubject', 'student'])->findOrFail($markId);
        $maxMarks = $examMark->examSubject->max_marks;

        // Validate mark range
        if ($validated['marks_obtained'] > $maxMarks) {
            return response()->json([
                'status' => 'error',
                'message' => "Mark cannot exceed maximum of {$maxMarks}."
            ], 422);
        }

        // Store old value for audit
        $oldMarks = $examMark->marks_obtained;
        $oldGrade = $examMark->grade;
        $oldStatus = $examMark->status;
        $percentage = ($validated['marks_obtained'] / $maxMarks) * 100;
        $grade = $this->calculateGrade($percentage);

        // Update mark WITHOUT changing status
        $examMark->update([
            'marks_obtained' => $validated['marks_obtained'],
            'percentage' => round($percentage, 2),
            'grade' => $grade,
            'remarks' => $validated['edit_reason'],
            'updated_at' => now(),
            // Keep existing status and approval data
        ]);

        // Log the change for audit trail
        Log::info('Admin edited mark', [
            'mark_id' => $markId,
            'student_id' => $examMark->student_id,
            'student_name' => $examMark->student->name ?? 'Unknown',
            'exam_id' => $examMark->exam_id,
            'subject_id' => $examMark->examSubject->subject_id,
            'old_marks' => $oldMarks,
            'new_marks' => $validated['marks_obtained'],
            'old_grade' => $oldGrade,
            'new_grade' => $grade,
            'status' => $oldStatus,
            'reason' => $validated['edit_reason'],
            'admin_id' => Auth::guard('employee')->id() ?? Auth::id(),
            'admin_name' => (Auth::guard('employee')->user() ?? Auth::user())->name ?? 'Admin',
            'reason' => $validated['edit_reason']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mark updated successfully. Change has been logged for audit.',
            'data' => [
                'id' => $examMark->id,
                'old_marks' => $oldMarks,
                'new_marks' => $validated['marks_obtained'],
                'old_grade' => $oldGrade,
                'new_grade' => $grade,
                'percentage' => round($percentage, 2),
                'status' => $examMark->status
            ]
        ]);
    }
    /**
     * Get existing marks for a specific exam, class, and subject
     * Used in EnterMarks.vue
     */
    public function existingMarks(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:ranks,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $examId = $request->exam_id;
        $classId = $request->class_id;
        $subjectId = $request->subject_id;

        // Get the exam subject ID
        $examSubject = ExamSubject::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$examSubject) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $marks = ExamMark::with(['skillMarks'])
            ->where('exam_subject_id', $examSubject->id)
            ->get()
            ->map(function ($mark) {
                return [
                    'id' => $mark->id,
                    'student_id' => $mark->student_id,
                    'overall_marks' => $mark->marks_obtained,
                    'status' => $mark->status,
                    'remarks' => $mark->remarks,
                    'skill_marks' => $mark->skillMarks->map(function ($skillMark) {
                        return [
                            'id' => $skillMark->id,
                            'exam_subject_skill_id' => $skillMark->exam_subject_skill_id,
                            'marks_obtained' => $skillMark->marks_obtained
                        ];
                    })
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $marks
        ]);
    }

    /**
     * Get subject skills for a specific exam, class, and subject
     * Used in EnterMarks.vue
     */
    public function examSubjectSkills(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:ranks,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $examId = $request->exam_id;
        $classId = $request->class_id;
        $subjectId = $request->subject_id;

        // Get the exam subject
        $examSubject = ExamSubject::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$examSubject) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        // Get skills for this exam subject
        $skills = \App\Models\ExamSubjectSkill::where('exam_subject_id', $examSubject->id)
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $skills
        ]);
    }
}
