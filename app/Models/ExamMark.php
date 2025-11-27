<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ExamMark extends Model
{
    const DRAFT = 'draft';
    const SUBMITTED = 'submitted';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const PUBLISHED = 'published';

    protected $fillable = [
        'exam_submission_id',
        'exam_subject_id',
        'student_id',
        'class_id',
        'teacher_id',
        'exam_id', // CRITICAL: Make sure this is included
        'marks_obtained',
        'maximum_marks',
        'grade',
        'status',
        'remarks',
        'rejection_reason',
        'submitted_by',
        'approved_by',
        'rejected_by',
        'submitted_at',
        'approved_at',
        'rejected_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'marks_obtained' => 'decimal:2',
        'maximum_marks' => 'decimal:2',
    ];

    /**
     * Boot method to automatically calculate grade when marks are set
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Set maximum_marks from exam_subject if not set
            if (!$model->maximum_marks && $model->examSubject) {
                $model->maximum_marks = $model->examSubject->max_marks;
            }

            // Calculate grade if marks are provided
            if ($model->marks_obtained && $model->maximum_marks) {
                $model->grade = $model->calculateGrade();
            }
        });

        // Auto-set teacher_id if not provided
        static::creating(function ($model) {
            if (!$model->teacher_id && Auth::check() && Auth::user()->isTeacher()) {
                $model->teacher_id = Auth::id();
            }
        });
    }

    /**
     * Get the exam submission that owns the exam mark.
     */
    public function examSubmission(): BelongsTo
    {
        return $this->belongsTo(ExamSubmission::class, 'exam_submission_id');
    }

    /**
     * Get the exam subject that owns the exam mark.
     */
    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class, 'exam_subject_id');
    }

    /**
     * Get the student that owns the exam mark.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the class that owns the exam mark.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'class_id');
    }

    /**
     * Get the teacher that owns the exam mark.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }

    /**
     * Get the exam that owns the exam mark.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    /**
     * Get the teacher who submitted the marks.
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the admin who approved the marks.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the admin who rejected the marks.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the skill marks for this exam mark.
     */
    public function skillMarks(): HasMany
    {
        return $this->hasMany(ExamSkillMark::class, 'exam_mark_id');
    }

    /**
     * Get the skill breakdown attribute.
     */
    public function getBreakdownAttribute()
    {
        // If skill marks exist, use actual teacher-entered data
        if ($this->skillMarks->isNotEmpty()) {
            return $this->skillMarks->map(function ($skillMark) {
                return [
                    'skill_name' => $skillMark->skill->skill_name,
                    'marks_obtained' => $skillMark->marks_obtained,
                    'maximum_marks' => $skillMark->skill->max_marks,
                    'remarks' => $this->getSkillRemarks($skillMark->marks_obtained, $skillMark->skill->max_marks)
                ];
            });
        }

        // Fallback: If no skill marks but overall marks exist, calculate breakdown
        if ($this->marks_obtained && $this->examSubject && $this->examSubject->subject) {
            return $this->generateCalculatedBreakdown();
        }

        return null;
    }

    /**
     * Generate calculated breakdown based on subject configuration
     */
    private function generateCalculatedBreakdown()
    {
        $subjectName = $this->examSubject->subject->name;
        $skillsConfig = config('exam_skills');
        $subjectSkills = $skillsConfig[$subjectName] ?? null;

        if (!$subjectSkills) {
            return null;
        }

        $breakdown = [];
        foreach ($subjectSkills as $skillName => $percentage) {
            $skillMaxMarks = round($this->maximum_marks * $percentage, 1);
            $skillMarks = round($this->marks_obtained * $percentage, 1);

            $breakdown[] = [
                'skill_name' => $skillName,
                'marks_obtained' => $skillMarks,
                'maximum_marks' => $skillMaxMarks,
                'remarks' => $this->getSkillRemarks($skillMarks, $skillMaxMarks)
            ];
        }

        return $breakdown;
    }

    /**
     * Get remarks for individual skills
     */
    private function getSkillRemarks($marks, $maxMarks)
    {
        if ($maxMarks == 0 || $marks === null) return 'Not assessed';

        $percentage = ($marks / $maxMarks) * 100;

        return match (true) {
            $percentage >= 80 => 'You exceeded expectations',
            $percentage >= 70 => 'You met expectations',
            $percentage >= 60 => 'You approached expectations',
            $percentage >= 50 => 'Satisfactory',
            default => 'You are below expectations'
        };
    }

    /**
     * Check if this mark has skill breakdown
     */
    public function hasSkillBreakdown(): bool
    {
        return $this->skillMarks->isNotEmpty();
    }

    /**
     * Get total marks from skill breakdown
     */
    public function getSkillBreakdownTotal(): float
    {
        return $this->skillMarks->sum('marks_obtained');
    }

    /**
     * Calculate the percentage for this mark.
     */
    public function percentage(): ?float
    {
        if (!$this->maximum_marks || $this->maximum_marks == 0) {
            return null;
        }

        return round(($this->marks_obtained / $this->maximum_marks) * 100, 2);
    }

    /**
     * Calculate and return grade based on percentage.
     */
    public function calculateGrade(): string
    {
        $percentage = $this->percentage();

        if ($percentage === null) {
            return 'N/A';
        }

        return \App\Services\GradingService::getGrade($percentage);
    }

    /**
     * Get the grade (alias for calculateGrade for backward compatibility).
     */
    public function getGradeAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return $this->calculateGrade();
    }

    /**
     * Check if marks are in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    /**
     * Check if marks are submitted for approval.
     */
    public function isSubmitted(): bool
    {
        return $this->status === self::SUBMITTED;
    }

    /**
     * Check if marks are approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    /**
     * Check if marks are rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::REJECTED;
    }

    /**
     * Check if marks are published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::PUBLISHED;
    }

    /**
     * Scope a query to only include draft marks.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::DRAFT);
    }

    /**
     * Scope a query to only include submitted marks.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', self::SUBMITTED);
    }

    /**
     * Scope a query to only include approved marks.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::APPROVED);
    }

    /**
     * Scope a query to only include rejected marks.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::REJECTED);
    }

    /**
     * Scope a query to only include published marks.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::PUBLISHED);
    }

    /**
     * Scope a query to only include marks awaiting approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::SUBMITTED);
    }

    /**
     * Scope a query to only include marks by exam and class.
     */
    public function scopeByExamAndClass($query, $examId, $classId)
    {
        return $query->where('exam_id', $examId)
            ->where('class_id', $classId);
    }

    /**
     * Scope a query to only include marks by teacher.
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope a query to only include marks by student.
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to only include marks by exam subject.
     */
    public function scopeByExamSubject($query, $examSubjectId)
    {
        return $query->where('exam_subject_id', $examSubjectId);
    }

    /**
     * Scope a query to only include marks with marks obtained.
     */
    public function scopeWithMarks($query)
    {
        return $query->whereNotNull('marks_obtained')
            ->where('marks_obtained', '>', 0);
    }

    /**
     * Scope a query to only include marks without marks obtained.
     */
    public function scopeWithoutMarks($query)
    {
        return $query->whereNull('marks_obtained')
            ->orWhere('marks_obtained', '<=', 0);
    }

    /**
     * Scope a query to only include marks with skill breakdown.
     */
    public function scopeWithSkillBreakdown($query)
    {
        return $query->whereHas('skillMarks');
    }

    /**
     * Scope a query to only include marks without skill breakdown.
     */
    public function scopeWithoutSkillBreakdown($query)
    {
        return $query->whereDoesntHave('skillMarks');
    }

    /**
     * Submit marks for approval.
     */
    public function submitForApproval(?int $submittedBy = null): bool
    {
        return $this->update([
            'status' => self::SUBMITTED,
            'submitted_by' => $submittedBy ?? Auth::id(),
            'submitted_at' => now(),
        ]);
    }

    /**
     * Approve marks.
     */
    public function approve(?int $approvedBy = null): bool
    {
        return $this->update([
            'status' => self::APPROVED,
            'approved_by' => $approvedBy ?? Auth::id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject marks.
     */
    public function reject(?int $rejectedBy = null, ?string $reason = null): bool
    {
        return $this->update([
            'status' => self::REJECTED,
            'rejected_by' => $rejectedBy ?? Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Publish marks.
     */
    public function publish(): bool
    {
        return $this->update([
            'status' => self::PUBLISHED,
        ]);
    }

    /**
     * Revert marks to draft.
     */
    public function revertToDraft(): bool
    {
        return $this->update([
            'status' => self::DRAFT,
            'submitted_by' => null,
            'submitted_at' => null,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Check if marks can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::DRAFT, self::REJECTED]);
    }

    /**
     * Check if marks can be submitted.
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === self::DRAFT;
    }

    /**
     * Check if marks can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::SUBMITTED;
    }

    /**
     * Check if marks can be rejected.
     */
    public function canBeRejected(): bool
    {
        return $this->status === self::SUBMITTED;
    }

    /**
     * Check if marks can be published.
     */
    public function canBePublished(): bool
    {
        return $this->status === self::APPROVED;
    }

    /**
     * Get the status with color for UI display.
     */
    public function getStatusWithColor(): array
    {
        return match ($this->status) {
            self::DRAFT => ['label' => 'Draft', 'color' => 'secondary', 'class' => 'bg-secondary'],
            self::SUBMITTED => ['label' => 'Submitted for Approval', 'color' => 'warning', 'class' => 'bg-warning'],
            self::APPROVED => ['label' => 'Approved', 'color' => 'success', 'class' => 'bg-success'],
            self::REJECTED => ['label' => 'Rejected', 'color' => 'danger', 'class' => 'bg-danger'],
            self::PUBLISHED => ['label' => 'Published', 'color' => 'primary', 'class' => 'bg-primary'],
            default => ['label' => 'Unknown', 'color' => 'dark', 'class' => 'bg-dark'],
        };
    }

    /**
     * Get formatted marks (e.g., "85/100")
     */
    public function getFormattedMarks(): string
    {
        if (!$this->maximum_marks) {
            return (string) $this->marks_obtained;
        }

        return $this->marks_obtained . '/' . $this->maximum_marks;
    }

    /**
     * Get formatted percentage
     */
    public function getFormattedPercentage(): string
    {
        $percentage = $this->percentage();
        return $percentage ? $percentage . '%' : 'N/A';
    }

    /**
     * Check if the mark is passing (assuming 40% is passing)
     */
    public function isPassing(): bool
    {
        $percentage = $this->percentage();
        return $percentage !== null && $percentage >= 40;
    }

    /**
     * Get the submission date in a formatted string
     */
    public function getFormattedSubmittedAt(): ?string
    {
        return $this->submitted_at?->format('M j, Y g:i A');
    }

    /**
     * Get the approval date in a formatted string
     */
    public function getFormattedApprovedAt(): ?string
    {
        return $this->approved_at?->format('M j, Y g:i A');
    }

    /**
     * Get the rejection date in a formatted string
     */
    public function getFormattedRejectedAt(): ?string
    {
        return $this->rejected_at?->format('M j, Y g:i A');
    }

    /**
     * Get the teacher's full name who submitted the marks
     */
    public function getSubmittedByName(): string
    {
        return $this->submittedBy ? $this->submittedBy->name : 'Unknown';
    }

    /**
     * Get the admin's full name who approved the marks
     */
    public function getApprovedByName(): string
    {
        return $this->approvedBy ? $this->approvedBy->name : 'Unknown';
    }

    /**
     * Get the admin's full name who rejected the marks
     */
    public function getRejectedByName(): string
    {
        return $this->rejectedBy ? $this->rejectedBy->name : 'Unknown';
    }

    /**
     * Bulk update status for multiple marks
     */
    public static function bulkUpdateStatus(array $markIds, string $status, ?int $userId = null): int
    {
        $updates = ['status' => $status];
        $userId = $userId ?? Auth::id();

        switch ($status) {
            case self::SUBMITTED:
                $updates['submitted_by'] = $userId;
                $updates['submitted_at'] = now();
                break;
            case self::APPROVED:
                $updates['approved_by'] = $userId;
                $updates['approved_at'] = now();
                $updates['rejected_by'] = null;
                $updates['rejected_at'] = null;
                $updates['rejection_reason'] = null;
                break;
            case self::REJECTED:
                $updates['rejected_by'] = $userId;
                $updates['rejected_at'] = now();
                $updates['approved_by'] = null;
                $updates['approved_at'] = null;
                break;
            case self::DRAFT:
                $updates['submitted_by'] = null;
                $updates['submitted_at'] = null;
                $updates['approved_by'] = null;
                $updates['approved_at'] = null;
                $updates['rejected_by'] = null;
                $updates['rejected_at'] = null;
                $updates['rejection_reason'] = null;
                break;
        }

        return self::whereIn('id', $markIds)->update($updates);
    }

    /**
     * Get marks summary for a submission
     */
    public static function getSubmissionSummary($examSubmissionId): array
    {
        $marks = self::where('exam_submission_id', $examSubmissionId)->get();

        return [
            'total_marks' => $marks->count(),
            'submitted_marks' => $marks->where('status', self::SUBMITTED)->count(),
            'approved_marks' => $marks->where('status', self::APPROVED)->count(),
            'rejected_marks' => $marks->where('status', self::REJECTED)->count(),
            'draft_marks' => $marks->where('status', self::DRAFT)->count(),
            'published_marks' => $marks->where('status', self::PUBLISHED)->count(),
            'average_percentage' => $marks->where('status', self::APPROVED)->avg(function ($mark) {
                return $mark->percentage();
            }),
        ];
    }

    /**
     * Get statistics for exam and class
     */
    public static function getExamClassStatistics($examId, $classId): array
    {
        $marks = self::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->get();

        $totalStudents = $marks->unique('student_id')->count();
        $totalSubjects = $marks->unique('exam_subject_id')->count();

        return [
            'total_marks' => $marks->count(),
            'total_students' => $totalStudents,
            'total_subjects' => $totalSubjects,
            'draft_marks' => $marks->where('status', self::DRAFT)->count(),
            'submitted_marks' => $marks->where('status', self::SUBMITTED)->count(),
            'approved_marks' => $marks->where('status', self::APPROVED)->count(),
            'published_marks' => $marks->where('status', self::PUBLISHED)->count(),
            'rejected_marks' => $marks->where('status', self::REJECTED)->count(),
            'completion_rate' => $totalStudents > 0 ?
                round((($marks->whereIn('status', [self::SUBMITTED, self::APPROVED, self::PUBLISHED])->count()) / ($totalStudents * $totalSubjects)) * 100, 2) : 0,
            'average_marks' => $marks->where('status', self::APPROVED)->avg('marks_obtained'),
            'marks_with_breakdown' => $marks->where('status', self::APPROVED)->filter(function ($mark) {
                return $mark->hasSkillBreakdown();
            })->count(),
        ];
    }

    /**
     * Find or create mark for student, subject, exam and class
     */
    public static function findOrCreateMark($studentId, $examSubjectId, $examId, $classId, $teacherId = null): self
    {
        return self::firstOrCreate(
            [
                'student_id' => $studentId,
                'exam_subject_id' => $examSubjectId,
                'exam_id' => $examId,
                'class_id' => $classId,
            ],
            [
                'teacher_id' => $teacherId ?? Auth::id(),
                'status' => self::DRAFT,
                'marks_obtained' => null,
            ]
        );
    }

    /**
     * Update or create multiple marks in bulk
     */
    public static function bulkUpsert(array $marksData): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => []
        ];

        foreach ($marksData as $data) {
            try {
                $mark = self::updateOrCreate(
                    [
                        'student_id' => $data['student_id'],
                        'exam_subject_id' => $data['exam_subject_id'],
                        'exam_id' => $data['exam_id'],
                        'class_id' => $data['class_id'],
                    ],
                    [
                        'marks_obtained' => $data['marks_obtained'],
                        'status' => $data['status'] ?? self::DRAFT,
                        'teacher_id' => $data['teacher_id'] ?? Auth::id(),
                        'remarks' => $data['remarks'] ?? null,
                        'submitted_by' => $data['submitted_by'] ?? null,
                        'submitted_at' => $data['submitted_at'] ?? null,
                        'approved_by' => $data['approved_by'] ?? null,
                        'approved_at' => $data['approved_at'] ?? null,
                    ]
                );

                if ($mark->wasRecentlyCreated) {
                    $results['created']++;
                } else {
                    $results['updated']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'data' => $data,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Check if marks exist for student in exam and class
     */
    public static function hasMarksForStudent($studentId, $examId, $classId): bool
    {
        return self::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->whereNotNull('marks_obtained')
            ->exists();
    }

    /**
     * Get student's total marks for an exam
     */
    public static function getStudentTotalMarks($studentId, $examId, $classId): float
    {
        return self::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->whereNotNull('marks_obtained')
            ->sum('marks_obtained');
    }

    /**
     * Get student's average percentage for an exam
     */
    public static function getStudentAveragePercentage($studentId, $examId, $classId): ?float
    {
        $marks = self::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->whereNotNull('marks_obtained')
            ->get();

        if ($marks->isEmpty()) {
            return null;
        }

        $totalPercentage = 0;
        $count = 0;

        foreach ($marks as $mark) {
            $percentage = $mark->percentage();
            if ($percentage !== null) {
                $totalPercentage += $percentage;
                $count++;
            }
        }

        return $count > 0 ? round($totalPercentage / $count, 2) : null;
    }

    /**
     * Add skill marks to this exam mark
     */
    public function addSkillMark($examSubjectSkillId, $marksObtained): ExamSkillMark
    {
        return ExamSkillMark::updateOrCreate(
            [
                'exam_mark_id' => $this->id,
                'exam_subject_skill_id' => $examSubjectSkillId,
            ],
            [
                'marks_obtained' => $marksObtained,
            ]
        );
    }

    /**
     * Remove all skill marks for this exam mark
     */
    public function clearSkillMarks(): bool
    {
        return $this->skillMarks()->delete();
    }

    /**
     * Sync skill marks for this exam mark
     */
    public function syncSkillMarks(array $skillMarks): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'errors' => []
        ];

        // Get current skill marks
        $currentSkillMarks = $this->skillMarks->pluck('exam_subject_skill_id')->toArray();
        $newSkillMarkIds = array_keys($skillMarks);

        // Delete removed skill marks
        $toDelete = array_diff($currentSkillMarks, $newSkillMarkIds);
        if (!empty($toDelete)) {
            $deleted = $this->skillMarks()->whereIn('exam_subject_skill_id', $toDelete)->delete();
            $results['deleted'] += $deleted;
        }

        // Update or create skill marks
        foreach ($skillMarks as $skillId => $marks) {
            try {
                $skillMark = ExamSkillMark::updateOrCreate(
                    [
                        'exam_mark_id' => $this->id,
                        'exam_subject_skill_id' => $skillId,
                    ],
                    [
                        'marks_obtained' => $marks,
                    ]
                );

                if ($skillMark->wasRecentlyCreated) {
                    $results['created']++;
                } else {
                    $results['updated']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'skill_id' => $skillId,
                    'marks' => $marks,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
