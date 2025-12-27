<?php

namespace App\Http\Controllers\Exams;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\ExamMark;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Employee;
use App\Models\Rank;
use App\Models\ExamSubject;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ApprovalQueueController extends Controller
{
    /**
     * Display the approval queue page
     */
    public function index(): Response
    {
        return Inertia::render('Exam/UploadExamResult/ApprovalQueue', [
            'title' => 'Marks Approval Queue'
        ]);
    }

    /**
     * Get pending submissions for approval - GROUPED BY TEACHER SUBMISSION
     */
    public function getPendingSubmissions(Request $request): JsonResponse
    {
        try {
            // Get pagination and search parameters
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            Log::info('Loading pending submissions from exam_marks...', [
                'search' => $search,
                'per_page' => $perPage,
                'page' => $page
            ]);

            // Get all submitted marks that need approval with proper relationships
            $query = ExamMark::with([
                'examSubject.exam:id,name',
                'examSubject.class:id,name',
                'examSubject.subject:id,name,code',
                'student:id,admission_number,first_name,last_name',
                'submittedBy:id,name,email'
            ])
                ->where('status', 'submitted')
                ->whereNotNull('submitted_by')
                ->whereNotNull('submitted_at');

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    // Search by teacher name
                    $q->whereHas('submittedBy', fn($q) => $q->where('name', 'like', "%{$search}%"))
                        // Search by exam, class, or subject
                        ->orWhereHas('examSubject', function ($q) use ($search) {
                            $q->whereHas('exam', fn($q) => $q->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('class', fn($q) => $q->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('subject', fn($q) => $q->where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%"));
                        })
                        // Search by student admission number or name
                        ->orWhereHas('student', fn($q) => $q->where('admission_number', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"));
                });
            }

            $pendingMarks = $query->get();

            Log::info('Total submitted marks found: ' . $pendingMarks->count());

            // If no marks found, return empty array
            if ($pendingMarks->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'meta' => [
                        'current_page' => (int)$page,
                        'per_page' => (int)$perPage,
                        'total' => 0,
                        'last_page' => 1
                    ],
                    'debug' => [
                        'source' => 'exam_marks_direct',
                        'total_marks' => 0,
                        'virtual_submissions' => 0,
                        'message' => 'No submitted marks found for approval'
                    ]
                ]);
            }

            // Group by exam_id, class_id, subject_id AND teacher_id to see teacher submissions
            $groupedSubmissions = $pendingMarks->groupBy(function ($mark) {
                $examId = $mark->examSubject->exam_id ?? 'unknown';
                $classId = $mark->examSubject->class_id ?? 'unknown';
                $subjectId = $mark->examSubject->subject_id ?? 'unknown';
                return $examId . '_' . $classId . '_' . $subjectId . '_' . $mark->submitted_by;
            })->map(function ($marks, $key) use ($search) {
                $firstMark = $marks->first();
                $examSubject = $firstMark->examSubject;

                // Safely get exam, class, and subject names with fallbacks
                $examName = $examSubject->exam->name ?? 'Unknown Exam';
                $className = $examSubject->class->name ?? 'Unknown Class';
                $subjectName = $examSubject->subject->name ?? 'Unknown Subject';
                $subjectCode = $examSubject->subject->code ?? 'N/A';

                // Calculate counts
                $studentsCount = $marks->unique('student_id')->count();
                $totalMarks = $marks->count();

                // Get teacher information with multiple fallback methods
                $teacherName = 'Unknown Teacher';
                $teacherEmail = 'N/A';

                // Method 1: Use submittedBy relationship
                if ($firstMark->submittedBy) {
                    $teacherName = $firstMark->submittedBy->name ?? 'Unknown Teacher';
                    $teacherEmail = $firstMark->submittedBy->email ?? 'N/A';
                }
                // Method 2: Try to find user directly
                else if ($firstMark->submitted_by) {
                    $user = User::find($firstMark->submitted_by);
                    if ($user) {
                        $teacherName = $user->name;
                        $teacherEmail = $user->email ?? 'N/A';
                    }
                }
                // Method 3: Try to get from employee table if teacher_id exists
                else if ($firstMark->teacher_id) {
                    $teacher = Employee::find($firstMark->teacher_id);
                    if ($teacher) {
                        $teacherName = $teacher->first_name . ' ' . $teacher->last_name;
                        $teacherEmail = $teacher->email ?? 'N/A';
                    }
                }

                // Identify matched students if searching
                $matchedStudents = [];
                if ($search) {
                    $matchedStudents = $marks->filter(function ($mark) use ($search) {
                        return stripos($mark->student->first_name, $search) !== false ||
                            stripos($mark->student->last_name, $search) !== false ||
                            stripos($mark->student->admission_number, $search) !== false;
                    })->map(function ($mark) {
                        return $mark->student->first_name . ' ' . $mark->student->last_name . ' (' . $mark->student->admission_number . ')';
                    })->unique()->values()->toArray();
                }

                return [
                    'id' => 'virtual_' . $key,
                    'exam_id' => $examSubject->exam_id ?? null,
                    'class_id' => $examSubject->class_id ?? null,
                    'subject_id' => $examSubject->subject_id ?? null,
                    'teacher_id' => $firstMark->submitted_by,
                    'exam_name' => $examName,
                    'class_name' => $className,
                    'subject_name' => $subjectName,
                    'subject_code' => $subjectCode,
                    'teacher_name' => $teacherName,
                    'teacher_email' => $teacherEmail,
                    'students_count' => $studentsCount,
                    'total_marks' => $totalMarks,
                    'submitted_date' => $firstMark->submitted_at->format('Y-m-d H:i:s'),
                    'submitted_at_formatted' => $firstMark->submitted_at->format('M j, Y g:i A'),
                    'matched_students' => $matchedStudents, // Show matched students if searching
                ];
            })->values();

            Log::info('Virtual submissions created: ' . $groupedSubmissions->count());

            // Manual Pagination of the grouped results
            $total = $groupedSubmissions->count();
            $paginatedItems = $groupedSubmissions->forPage($page, $perPage)->values();

            return response()->json([
                'data' => $paginatedItems,
                'meta' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage)
                ],
                'debug' => [
                    'source' => 'exam_marks_direct',
                    'total_marks' => $pendingMarks->count(),
                    'virtual_submissions' => $total,
                    'paginated_items' => $paginatedItems->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading pending submissions from marks: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to load pending submissions',
                'message' => $e->getMessage(),
                'debug' => 'Check Laravel logs for detailed error information'
            ], 500);
        }
    }

    /**
     * Get approved submissions history with search and pagination
     */
    public function getApprovedSubmissions(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = ExamMark::with([
                'examSubject.exam:id,name',
                'examSubject.class:id,name',
                'examSubject.subject:id,name,code',
                'submittedBy:id,name,email',
                'approvedBy:id,name',
                'student:id,first_name,last_name,admission_number'
            ])
                ->where('status', 'approved')
                ->whereNotNull('approved_at');

            // Apply Search
            if ($search) {
                $query->where(function ($q) use ($search) {
                    // Search by Exam, Class, Subject
                    $q->whereHas('examSubject', function ($q) use ($search) {
                        $q->whereHas('exam', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('class', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('subject', fn($q) => $q->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%"));
                    })
                        // Search by Teacher
                        ->orWhereHas('submittedBy', fn($q) => $q->where('name', 'like', "%{$search}%"))
                        // Search by Student (This is the key requirement)
                        ->orWhereHas('student', fn($q) => $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('admission_number', 'like', "%{$search}%"));
                });
            }

            // Get results - limit to reasonable amount for performance if no search, 
            // but if searching we need to scan more
            $limit = $search ? 5000 : 2000;
            $approvedMarks = $query->latest('approved_at')->limit($limit)->get();

            // Group by exam_id, class_id, subject_id AND teacher_id
            $groupedSubmissions = $approvedMarks->groupBy(function ($mark) {
                $examId = $mark->examSubject->exam_id ?? 'unknown';
                $classId = $mark->examSubject->class_id ?? 'unknown';
                $subjectId = $mark->examSubject->subject_id ?? 'unknown';
                return $examId . '_' . $classId . '_' . $subjectId . '_' . $mark->submitted_by;
            })->map(function ($marks, $key) use ($search) {
                $firstMark = $marks->first();
                $examSubject = $firstMark->examSubject;

                // Identify matched students if searching
                $matchedStudents = [];
                if ($search) {
                    $matchedStudents = $marks->filter(function ($mark) use ($search) {
                        return stripos($mark->student->first_name, $search) !== false ||
                            stripos($mark->student->last_name, $search) !== false ||
                            stripos($mark->student->admission_number, $search) !== false;
                    })->map(function ($mark) {
                        return $mark->student->first_name . ' ' . $mark->student->last_name;
                    })->unique()->values()->toArray();
                }

                return [
                    'id' => 'virtual_approved_' . $key,
                    'virtual_id_raw' => $key,
                    'exam_name' => $examSubject->exam->name ?? 'Unknown Exam',
                    'class_name' => $examSubject->class->name ?? 'Unknown Class',
                    'subject_name' => $examSubject->subject->name ?? 'Unknown Subject',
                    'subject_code' => $examSubject->subject->code ?? 'N/A',
                    'teacher_name' => $firstMark->submittedBy->name ?? 'Unknown Teacher',
                    'approved_by_name' => $firstMark->approvedBy->name ?? 'Unknown Admin',
                    'students_count' => $marks->unique('student_id')->count(),
                    'total_marks' => $marks->count(),
                    'submitted_date' => $firstMark->submitted_at ? $firstMark->submitted_at->format('Y-m-d H:i:s') : null,
                    'approved_date' => $firstMark->approved_at ? $firstMark->approved_at->format('Y-m-d H:i:s') : null,
                    'approved_at_formatted' => $firstMark->approved_at ? $firstMark->approved_at->format('M j, Y g:i A') : 'N/A',
                    'matched_students' => $matchedStudents, // Return matched students
                ];
            })->values();

            // Manual Pagination of the grouped results
            $total = $groupedSubmissions->count();
            $paginatedItems = $groupedSubmissions->forPage($page, $perPage)->values();

            return response()->json([
                'data' => $paginatedItems,
                'meta' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading approved submissions: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load approved submissions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approval statistics - COUNT UNIQUE SUBMISSIONS (not individual marks)
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            // Count unique submissions by grouping exam_subject_id + submitted_by
            $pending = ExamMark::where('status', 'submitted')
                ->whereNotNull('submitted_by')
                ->select('exam_subject_id', 'submitted_by')
                ->distinct()
                ->get()
                ->count();

            $approved = ExamMark::where('status', 'approved')
                ->whereNotNull('approved_by')
                ->select('exam_subject_id', 'submitted_by')
                ->distinct()
                ->get()
                ->count();

            $rejected = ExamMark::where('status', 'rejected')
                ->whereNotNull('submitted_by')
                ->select('exam_subject_id', 'submitted_by')
                ->distinct()
                ->get()
                ->count();

            // Total processed is approved + rejected
            $total = $approved + $rejected;

            $stats = [
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
                'total' => $total,
            ];

            return response()->json([
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading approval stats: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed marks for a teacher's submission - WITH PER-STUDENT APPROVAL OPTIONS
     */
    public function getSubmissionDetails($submissionId): JsonResponse
    {
        try {
            // Check if this is an approved submission request
            $isApproved = str_contains($submissionId, 'virtual_approved_');
            $cleanId = str_replace(['virtual_approved_', 'virtual_'], '', $submissionId);

            // Extract exam_id, class_id, subject_id, and teacher_id from virtual ID
            $parts = explode('_', $cleanId);
            if (count($parts) !== 4) {
                throw new \Exception('Invalid submission ID format');
            }

            list($examId, $classId, $subjectId, $teacherId) = $parts;

            $query = ExamMark::with([
                'student:id,admission_number,first_name,last_name',
                'examSubject.subject:id,name,code',
                'examSubject.exam:id,name',
                'examSubject.class:id,name',
                'submittedBy:id,name,email'
            ])
                ->whereHas('examSubject', function ($query) use ($examId, $classId, $subjectId) {
                    $query->where('exam_id', $examId)
                        ->where('class_id', $classId)
                        ->where('subject_id', $subjectId);
                })
                ->where('submitted_by', $teacherId);

            // Filter by status based on the ID type
            if ($isApproved) {
                $query->where('status', 'approved');
            } else {
                $query->where('status', 'submitted');
            }

            $marks = $query->get();

            if ($marks->isEmpty()) {
                return response()->json([
                    'error' => 'No marks found for this submission',
                    'message' => 'The submission may have been already processed'
                ], 404);
            }

            // Calculate summary
            $studentsCount = $marks->unique('student_id')->count();
            $firstMark = $marks->first();

            // Get teacher information with fallbacks
            $teacherName = 'Unknown Teacher';
            $teacherEmail = 'N/A';

            if ($firstMark->submittedBy) {
                $teacherName = $firstMark->submittedBy->name ?? 'Unknown Teacher';
                $teacherEmail = $firstMark->submittedBy->email ?? 'N/A';
            } else if ($firstMark->submitted_by) {
                $user = User::find($firstMark->submitted_by);
                if ($user) {
                    $teacherName = $user->name;
                    $teacherEmail = $user->email ?? 'N/A';
                }
            }

            $detailedMarks = $marks->map(function ($mark) {
                $maxMarks = $mark->maximum_marks ?? $mark->examSubject->max_marks ?? 100;
                $percentage = $maxMarks > 0 ? round(($mark->marks_obtained / $maxMarks) * 100, 2) : 0;

                // Calculate grade with fallback - use model's calculateGrade method
                $grade = $mark->grade;
                if (!$grade || $grade === 'N/A') {
                    $grade = $mark->calculateGrade();
                }

                return [
                    'id' => $mark->id, // Individual mark ID for per-student approval
                    'student_id' => $mark->student_id,
                    'student_name' => $mark->student ?
                        ($mark->student->first_name . ' ' . $mark->student->last_name) :
                        'Unknown Student',
                    'admission_number' => $mark->student->admission_number ?? 'N/A',
                    'subject_name' => $mark->examSubject->subject->name ?? 'Unknown Subject',
                    'subject_code' => $mark->examSubject->subject->code ?? 'N/A',
                    'marks_obtained' => $mark->marks_obtained,
                    'maximum_marks' => $maxMarks,
                    'grade' => $grade,
                    'percentage' => $percentage,
                    'formatted_marks' => $mark->marks_obtained . '/' . $maxMarks,
                    'formatted_percentage' => $percentage . '%',
                    'is_passing' => $mark->marks_obtained >= ($mark->passing_marks ?? ($maxMarks * 0.4)),
                    'remarks' => $mark->remarks,
                    'status' => $mark->status,
                    'can_approve_individual' => true, // Flag for frontend to show individual approval
                    'debug_info' => [
                        'has_max_marks' => !is_null($maxMarks),
                        'calculated_grade' => $grade,
                        'original_grade' => $mark->getRawOriginal('grade'),
                    ]
                ];
            });

            return response()->json([
                'data' => [
                    'marks' => $detailedMarks,
                    'summary' => [
                        'total_students' => $studentsCount,
                        'total_marks' => $marks->count(),
                        'average_percentage' => $detailedMarks->avg('percentage') ?? 0,
                    ],
                    'submission_info' => [
                        'id' => $submissionId,
                        'exam_name' => $firstMark->examSubject->exam->name ?? 'Unknown Exam',
                        'class_name' => $firstMark->examSubject->class->name ?? 'Unknown Class',
                        'subject_name' => $firstMark->examSubject->subject->name ?? 'Unknown Subject',
                        'subject_code' => $firstMark->examSubject->subject->code ?? 'N/A',
                        'teacher_name' => $teacherName,
                        'teacher_email' => $teacherEmail,
                        'submitted_at' => $firstMark->submitted_at->format('Y-m-d H:i:s'),
                        'submitted_at_formatted' => $firstMark->submitted_at->format('M j, Y g:i A'),
                        'students_count' => $studentsCount,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading submission details: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load submission details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve ALL marks in a teacher's submission (Bulk approval)
     */
    public function approveMarks(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'submission_ids' => 'required|array',
                'submission_ids.*' => 'string' // Virtual IDs
            ]);

            $approvedCount = 0;
            $totalMarksApproved = 0;

            foreach ($request->submission_ids as $virtualId) {
                // Extract exam_id, class_id, subject_id, and teacher_id from virtual ID
                $parts = explode('_', str_replace('virtual_', '', $virtualId));
                if (count($parts) !== 4) continue;

                list($examId, $classId, $subjectId, $teacherId) = $parts;

                // Update all marks for this teacher's submission
                $marksUpdated = ExamMark::whereHas('examSubject', function ($query) use ($examId, $classId, $subjectId) {
                    $query->where('exam_id', $examId)
                        ->where('class_id', $classId)
                        ->where('subject_id', $subjectId);
                })
                    ->where('submitted_by', $teacherId)
                    ->where('status', 'submitted')
                    ->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                $totalMarksApproved += $marksUpdated;
                if ($marksUpdated > 0) {
                    $approvedCount++;
                }
            }

            DB::commit();

            Log::info('Exam marks approved in bulk', [
                'user_id' => auth()->id(),
                'virtual_submission_ids' => $request->submission_ids,
                'approved_count' => $approvedCount,
                'marks_approved' => $totalMarksApproved
            ]);

            return response()->json([
                'message' => $approvedCount . ' teacher submission(s) approved successfully. ' . $totalMarksApproved . ' marks approved.',
                'approved_count' => $approvedCount,
                'marks_approved' => $totalMarksApproved
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving marks: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to approve marks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject ALL marks in a teacher's submission (Bulk rejection)
     */
    public function rejectMarks(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'submission_ids' => 'required|array',
                'submission_ids.*' => 'string', // Virtual IDs
                'rejection_reason' => 'required|string|min:10|max:500'
            ]);

            $rejectedCount = 0;
            $totalMarksRejected = 0;
            $rejectionReason = $request->rejection_reason;

            foreach ($request->submission_ids as $virtualId) {
                // Extract exam_id, class_id, subject_id, and teacher_id from virtual ID
                $parts = explode('_', str_replace('virtual_', '', $virtualId));
                if (count($parts) !== 4) continue;

                list($examId, $classId, $subjectId, $teacherId) = $parts;

                // Update all marks for this teacher's submission
                $marksUpdated = ExamMark::whereHas('examSubject', function ($query) use ($examId, $classId, $subjectId) {
                    $query->where('exam_id', $examId)
                        ->where('class_id', $classId)
                        ->where('subject_id', $subjectId);
                })
                    ->where('submitted_by', $teacherId)
                    ->where('status', 'submitted')
                    ->update([
                        'status' => 'rejected',
                        'remarks' => $rejectionReason,
                    ]);

                $totalMarksRejected += $marksUpdated;
                if ($marksUpdated > 0) {
                    $rejectedCount++;
                }
            }

            DB::commit();

            Log::info('Exam marks rejected in bulk', [
                'user_id' => auth()->id(),
                'virtual_submission_ids' => $request->submission_ids,
                'rejected_count' => $rejectedCount,
                'marks_rejected' => $totalMarksRejected,
                'reason' => $rejectionReason
            ]);

            return response()->json([
                'message' => $rejectedCount . ' teacher submission(s) rejected successfully. ' . $totalMarksRejected . ' marks rejected.',
                'rejected_count' => $rejectedCount,
                'marks_rejected' => $totalMarksRejected
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting marks: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to reject marks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * APPROVE INDIVIDUAL STUDENT MARKS
     */
    public function approveStudentMarks(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'mark_ids' => 'required|array',
                'mark_ids.*' => 'exists:exam_marks,id'
            ]);

            $approvedCount = ExamMark::whereIn('id', $request->mark_ids)
                ->where('status', 'submitted')
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

            DB::commit();

            Log::info('Individual student marks approved', [
                'user_id' => auth()->id(),
                'mark_ids' => $request->mark_ids,
                'approved_count' => $approvedCount
            ]);

            return response()->json([
                'message' => $approvedCount . ' student mark(s) approved successfully.',
                'approved_count' => $approvedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving individual student marks: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to approve student marks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * REJECT INDIVIDUAL STUDENT MARKS
     */
    public function rejectStudentMarks(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'mark_ids' => 'required|array',
                'mark_ids.*' => 'exists:exam_marks,id',
                'rejection_reason' => 'required|string|min:10|max:500'
            ]);

            $rejectedCount = ExamMark::whereIn('id', $request->mark_ids)
                ->where('status', 'submitted')
                ->update([
                    'status' => 'rejected',
                    'remarks' => $request->rejection_reason,
                ]);

            DB::commit();

            Log::info('Individual student marks rejected', [
                'user_id' => auth()->id(),
                'mark_ids' => $request->mark_ids,
                'rejected_count' => $rejectedCount,
                'reason' => $request->rejection_reason
            ]);

            return response()->json([
                'message' => $rejectedCount . ' student mark(s) rejected successfully.',
                'rejected_count' => $rejectedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting individual student marks: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to reject student marks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve all pending submissions for a specific exam and class
     */
    public function bulkApproveExamClass(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'exam_id' => 'required|exists:exams,id',
                'class_id' => 'required|exists:classes,id'
            ]);

            $marksUpdated = ExamMark::whereHas('examSubject', function ($query) use ($request) {
                $query->where('exam_id', $request->exam_id)
                    ->where('class_id', $request->class_id);
            })
                ->where('status', 'submitted')
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'message' => 'All pending submissions for this exam and class have been approved. ' . $marksUpdated . ' marks approved.',
                'marks_approved' => $marksUpdated
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk approval: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to bulk approve',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix missing grades for submitted marks
     */
    /**
     * Fix missing grades for submitted marks
     */
    public function fixMissingGrades(): JsonResponse
    {
        try {
            $fixedCount = DB::transaction(function () {
                $marks = ExamMark::where('status', 'submitted')
                    ->where(function ($query) {
                        $query->whereNull('grade')
                            ->orWhere('grade', 'N/A');
                    })
                    ->whereNotNull('marks_obtained')
                    ->whereNotNull('maximum_marks')
                    ->where('maximum_marks', '>', 0)
                    ->get();

                $fixed = 0;
                foreach ($marks as $mark) {
                    $grade = $mark->calculateGrade();
                    if ($grade && $grade !== 'N/A') {
                        $mark->update(['grade' => $grade]);
                        $fixed++;
                    }
                }
                return $fixed;
            });

            return response()->json([
                'message' => "Fixed grades for {$fixedCount} marks.",
                'fixed_count' => $fixedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error fixing missing grades: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fix grades',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update approved mark (Admin only) - for correcting errors
     */
    public function updateApprovedMark(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Validate request
            $validated = $request->validate([
                'mark_id' => 'required|exists:exam_marks,id',
                'new_marks' => 'required|numeric|min:0',
                'reason' => 'required|string|min:10|max:500'
            ]);

            // Check if user is admin
            $user = auth()->user();
            if (!$user->isAbleTo('edit-approved-marks')) {
                return response()->json(['error' => 'Unauthorized. Missing edit-approved-marks permission.'], 403);
            }

            // Get the mark
            $mark = ExamMark::findOrFail($validated['mark_id']);

            // Validate mark status
            if ($mark->status !== 'approved') {
                return response()->json([
                    'error' => 'Invalid status',
                    'message' => 'Only approved marks can be edited'
                ], 400);
            }

            // Validate new marks is within range
            $maxMarks = $mark->maximum_marks ?? $mark->examSubject->max_marks ?? 100;
            if ($validated['new_marks'] > $maxMarks) {
                return response()->json([
                    'error' => 'Invalid marks',
                    'message' => "Marks cannot exceed maximum marks ({$maxMarks})"
                ], 400);
            }

            // Store old values for audit log
            $oldMarks = $mark->marks_obtained;
            $oldGrade = $mark->grade;
            $oldPercentage = $maxMarks > 0 ? round(($oldMarks / $maxMarks) * 100, 2) : 0;

            // Update marks
            $mark->marks_obtained = $validated['new_marks'];

            // Recalculate grade and percentage
            $newPercentage = $maxMarks > 0 ? round(($validated['new_marks'] / $maxMarks) * 100, 2) : 0;
            $newGrade = $mark->calculateGrade();
            $mark->grade = $newGrade;

            // Add edit reason to remarks
            $editNote = "\n[ADMIN EDIT by {$user->name} on " . now()->format('Y-m-d H:i') . "]: {$validated['reason']}";
            $mark->remarks = ($mark->remarks ?? '') . $editNote;

            $mark->save();

            // Log the change
            Log::info('Admin edited approved mark', [
                'admin_id' => $user->id,
                'admin_name' => $user->name,
                'mark_id' => $mark->id,
                'student_id' => $mark->student_id,
                'old_marks' => $oldMarks,
                'new_marks' => $validated['new_marks'],
                'old_grade' => $oldGrade,
                'new_grade' => $newGrade,
                'reason' => $validated['reason']
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Mark updated successfully',
                'data' => [
                    'id' => $mark->id,
                    'old_marks' => $oldMarks,
                    'new_marks' => $validated['new_marks'],
                    'old_grade' => $oldGrade,
                    'new_grade' => $newGrade,
                    'old_percentage' => $oldPercentage,
                    'new_percentage' => $newPercentage,
                    'maximum_marks' => $maxMarks
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating approved mark: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update mark',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
