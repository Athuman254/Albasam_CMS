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
                        $q->where('name', 'like', "%{$value}%")
                            ->orWhere('admission_number', 'like', "%{$value}%");
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
        // FIX: Convert frontend array format to backend object format
        $convertedMarks = [];
        if ($request->has('marks') && is_array($request->marks)) {
            if (isset($request->marks[0]) && is_array($request->marks[0])) {
                // Frontend format: [{student_id: 1, marks: 85}, {student_id: 2, marks: 90}]
                foreach ($request->marks as $markData) {
                    if (isset($markData['student_id']) && isset($markData['marks'])) {
                        $studentId = (int)$markData['student_id'];
                        $convertedMarks[$studentId] = $markData['marks'];
                    }
                }
            } else {
                // Backend format: {1: 85, 2: 90} - keep as is
                $convertedMarks = $request->marks;
            }
            $request->merge(['marks' => $convertedMarks]);
        }

        // FIX: Convert all numeric fields to proper types before validation
        $request->merge([
            'exam_id' => (int)$request->exam_id,
            'class_id' => (int)$request->class_id,
            'subject_id' => (int)$request->subject_id,
        ]);

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|integer|exists:ranks,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required|array',
            'marks.*' => 'nullable|numeric|min:0',
            'submit_for_approval' => 'sometimes|boolean',
            'remarks' => 'nullable|string|max:500',
            'is_bulk_upload' => 'sometimes|boolean',
        ]);

        $examId = $validated['exam_id'];
        $classId = $validated['class_id'];
        $subjectId = $validated['subject_id'];
        $marks = $validated['marks'];
        $submitForApproval = $validated['submit_for_approval'] ?? false;
        $remarks = $validated['remarks'] ?? null;
        $isBulkUpload = $validated['is_bulk_upload'] ?? false;

        // Verify teacher has access to this subject in this class
        $hasAccess = EmployeeClass::where('employee_id', auth()->id())
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have access to enter marks for this subject in this class.',
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
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Reload with subject relationship
                $examSubject->load('subject');

                \Log::info('Auto-created ExamSubject', [
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'exam_subject_id' => $examSubject->id,
                    'teacher_id' => auth()->id()
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create ExamSubject', [
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
                $errors[] = [
                    'student_id' => (int)$studentId,
                    'message' => "Mark must be a number.",
                ];
                continue;
            }

            $score = (float) $score;

            if ($score < 0 || $score > $maxMarks) {
                $errors[] = [
                    'student_id' => (int)$studentId,
                    'message' => "Mark must be between 0 and {$maxMarks}.",
                ];
                continue;
            }

            $status = $submitForApproval ? ExamMark::SUBMITTED : ExamMark::DRAFT;

            // Calculate grade based on percentage
            $percentage = ($score / $maxMarks) * 100;
            $grade = $this->calculateGrade($percentage);

            $toSave[] = [
                'exam_subject_id' => $examSubject->id,
                'student_id' => $studentId,
                'marks_obtained' => $score,
                'maximum_marks' => $maxMarks,
                'percentage' => round($percentage, 2),
                'grade' => $grade,
                'status' => $status,
                'remarks' => $remarks,
                'exam_id' => $examId,
                'class_id' => $classId,
                'teacher_id' => auth()->id(),
                'submitted_by' => $submitForApproval ? auth()->id() : null,
                'submitted_at' => $submitForApproval ? $now : null,
                'approved_by' => null,
                'approved_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($errors)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Some marks were invalid',
                'errors' => $errors,
            ], 422);
        }

        DB::transaction(function () use ($toSave, $examId, $classId, $examSubject) {
            foreach ($toSave as $row) {
                ExamMark::updateOrCreate(
                    [
                        'exam_subject_id' => $row['exam_subject_id'],
                        'student_id' => $row['student_id'],
                        'exam_id' => $examId,
                        'class_id' => $classId,
                    ],
                    $row
                );
            }
        });

        $message = $submitForApproval ?
            'Marks saved and submitted for approval successfully!' :
            'Marks saved as draft successfully!';

        // Add info about auto-created exam subject if it was created
        $additionalInfo = '';
        if (!$examSubject->wasRecentlyCreated) {
            $additionalInfo = ' Exam subject configuration was automatically created.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message . $additionalInfo,
            'data' => [
                'submitted_for_approval' => $submitForApproval,
                'students_updated' => count($toSave),
                'subject' => $examSubject->subject->name,
                'max_marks' => $maxMarks,
                'exam_subject_id' => $examSubject->id,
                'exam_subject_created' => $examSubject->wasRecentlyCreated
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

        // Verify teacher has access to this subject in this class
        $hasAccess = EmployeeClass::where('employee_id', auth()->id())
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have access to enter marks for this subject in this class.',
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
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                \Log::info('Auto-created ExamSubject for single mark', [
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'exam_subject_id' => $examSubject->id,
                    'teacher_id' => auth()->id()
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create ExamSubject for single mark', [
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
                'teacher_id' => auth()->id(),
                'submitted_by' => $submitForApproval ? auth()->id() : null,
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
                    'created_by' => auth()->id(),
                ]);

                \Log::info('Auto-created ExamSubject for bulk submission', [
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'exam_subject_id' => $examSubject->id
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

        $updatedCount = $query->update([
            'status' => ExamMark::SUBMITTED,
            'submitted_by' => auth()->id(),
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

        $updatedCount = ExamMark::whereIn('id', $markIds)
            ->where('status', ExamMark::SUBMITTED)
            ->update([
                'status' => ExamMark::APPROVED,
                'approved_by' => auth()->id(),
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

        $updatedCount = ExamMark::whereIn('id', $markIds)
            ->where('status', ExamMark::SUBMITTED)
            ->update([
                'status' => ExamMark::REJECTED,
                'remarks' => $rejectionReason,
                'approved_by' => auth()->id(),
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

        $subjects = EmployeeClass::with('subject')
            ->where('employee_id', auth()->id())
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

            // Check if user has permission to delete
            if ($mark->teacher_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
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
        if (!auth()->user()->hasRole('admin')) {
            $foreignMarks = ExamMark::whereIn('id', $markIds)
                ->where('teacher_id', '!=', auth()->id())
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

        $teacherId = auth()->id();
        $examId = $request->exam_id;
        $classId = $request->class_id;
        $subjectId = $request->subject_id;

        $query = ExamMark::where('teacher_id', $teacherId);

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

        // Check permissions
        if ($mark->teacher_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
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
        // Verify admin role
        if (!auth()->user()->hasRole('admin')) {
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
        \Log::info('Admin edited mark', [
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
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
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
}
