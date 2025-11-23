<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ExamSubmission extends Model
{
    const DRAFT = 'draft';
    const SUBMITTED = 'submitted';
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const PUBLISHED = 'published';

    protected $fillable = [
        'exam_id',
        'class_id',
        'teacher_id',
        'submitted_by', 
        'academic_year_id',
        'students_count',
        'subjects_count',
        'marks_entered_count',
        'total_marks_count',
        'submitted_at',
        'status',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'remarks',
        'completion_percentage',
        'is_bulk_submission'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completion_percentage' => 'decimal:2',
        'is_bulk_submission' => 'boolean',
        'students_count' => 'integer',
        'subjects_count' => 'integer',
        'marks_entered_count' => 'integer',
        'total_marks_count' => 'integer',
    ];

    /**
     * Boot method for automatic model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set teacher_id if not provided
        static::creating(function ($model) {
            if (!$model->teacher_id && Auth::check()) {
                $model->teacher_id = Auth::id();
            }

            // Auto-set submitted_by if not provided
            if (!$model->submitted_by && Auth::check()) {
                $model->submitted_by = Auth::id();
            }

            // Calculate total marks count
            if (!$model->total_marks_count && $model->students_count && $model->subjects_count) {
                $model->total_marks_count = $model->students_count * $model->subjects_count;
            }

            // Calculate completion percentage
            if ($model->total_marks_count > 0) {
                $model->completion_percentage = round(($model->marks_entered_count / $model->total_marks_count) * 100, 2);
            }
        });

        // Update completion percentage when marks are updated
        static::updating(function ($model) {
            if ($model->total_marks_count > 0) {
                $model->completion_percentage = round(($model->marks_entered_count / $model->total_marks_count) * 100, 2);
            }
        });
    }

    /**
     * Get the exam that owns the submission.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    /**
     * Get the class that owns the submission.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'class_id');
    }

    /**
     * Get the teacher that owns the submission.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }

    /**
     * Get the user who submitted the submission.
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the academic year that owns the submission.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get the admin who approved the submission.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the admin who rejected the submission.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the exam marks for the submission.
     */
    public function examMarks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'exam_submission_id');
    }

    /**
     * Get the exam marks with student and subject details.
     */
    public function examMarksWithDetails(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'exam_submission_id')
            ->with(['student', 'examSubject.subject']);
    }

    /**
     * Check if submission is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    /**
     * Check if submission is submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->status === self::SUBMITTED;
    }

    /**
     * Check if submission is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    /**
     * Check if submission is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    /**
     * Check if submission is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::REJECTED;
    }

    /**
     * Check if submission is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::PUBLISHED;
    }

    /**
     * Check if submission is completed (100% marks entered).
     */
    public function isCompleted(): bool
    {
        return $this->completion_percentage >= 100;
    }

    /**
     * Check if submission can be submitted.
     */
    public function canBeSubmitted(): bool
    {
        return in_array($this->status, [self::DRAFT, self::REJECTED]) && $this->isCompleted();
    }

    /**
     * Check if submission can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::SUBMITTED || $this->status === self::PENDING;
    }

    /**
     * Check if submission can be rejected.
     */
    public function canBeRejected(): bool
    {
        return $this->status === self::SUBMITTED || $this->status === self::PENDING;
    }

    /**
     * Check if submission can be published.
     */
    public function canBePublished(): bool
    {
        return $this->status === self::APPROVED;
    }

    /**
     * Check if submission can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::DRAFT, self::REJECTED]);
    }

    /**
     * Submit the submission for approval.
     */
    public function submitForApproval(): bool
    {
        return $this->update([
            'status' => self::SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Approve the submission.
     */
    public function approve(?int $approvedBy = null): bool
    {
        return $this->update([
            'status' => self::APPROVED,
            'approved_by' => $approvedBy ?? auth()->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject the submission.
     */
    public function reject(?int $rejectedBy = null, ?string $reason = null): bool
    {
        return $this->update([
            'status' => self::REJECTED,
            'rejected_by' => $rejectedBy ?? auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Publish the submission.
     */
    public function publish(): bool
    {
        return $this->update([
            'status' => self::PUBLISHED,
        ]);
    }

    /**
     * Revert to draft.
     */
    public function revertToDraft(): bool
    {
        return $this->update([
            'status' => self::DRAFT,
            'submitted_at' => null,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Update marks count and recalculate completion.
     */
    public function updateMarksCount(int $enteredCount): bool
    {
        $this->marks_entered_count = $enteredCount;
        
        if ($this->total_marks_count > 0) {
            $this->completion_percentage = round(($enteredCount / $this->total_marks_count) * 100, 2);
        }
        
        return $this->save();
    }

    /**
     * Recalculate all counts based on actual marks.
     */
    public function recalculateCounts(): bool
    {
        $marksCount = $this->examMarks()->whereNotNull('marks_obtained')->count();
        $studentsCount = $this->examMarks()->distinct('student_id')->count('student_id');
        $subjectsCount = $this->examMarks()->distinct('exam_subject_id')->count('exam_subject_id');
        
        return $this->update([
            'marks_entered_count' => $marksCount,
            'students_count' => $studentsCount,
            'subjects_count' => $subjectsCount,
            'total_marks_count' => $studentsCount * $subjectsCount,
        ]);
    }

    /**
     * Get the status with color for UI display.
     */
    public function getStatusWithColor(): array
    {
        return match ($this->status) {
            self::DRAFT => ['label' => 'Draft', 'color' => 'secondary', 'class' => 'bg-secondary'],
            self::SUBMITTED => ['label' => 'Submitted', 'color' => 'warning', 'class' => 'bg-warning'],
            self::PENDING => ['label' => 'Pending Approval', 'color' => 'info', 'class' => 'bg-info'],
            self::APPROVED => ['label' => 'Approved', 'color' => 'success', 'class' => 'bg-success'],
            self::REJECTED => ['label' => 'Rejected', 'color' => 'danger', 'class' => 'bg-danger'],
            self::PUBLISHED => ['label' => 'Published', 'color' => 'primary', 'class' => 'bg-primary'],
            default => ['label' => 'Unknown', 'color' => 'dark', 'class' => 'bg-dark'],
        };
    }

    /**
     * Get completion status with color.
     */
    public function getCompletionStatus(): array
    {
        if ($this->completion_percentage >= 100) {
            return ['label' => 'Completed', 'color' => 'success', 'class' => 'bg-success'];
        } elseif ($this->completion_percentage >= 75) {
            return ['label' => 'Almost Complete', 'color' => 'info', 'class' => 'bg-info'];
        } elseif ($this->completion_percentage >= 50) {
            return ['label' => 'In Progress', 'color' => 'warning', 'class' => 'bg-warning'];
        } else {
            return ['label' => 'Just Started', 'color' => 'secondary', 'class' => 'bg-secondary'];
        }
    }

    /**
     * Scope a query to only include draft submissions.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::DRAFT);
    }

    /**
     * Scope a query to only include submitted submissions.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', self::SUBMITTED);
    }

    /**
     * Scope a query to only include pending submissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::PENDING);
    }

    /**
     * Scope a query to only include approved submissions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::APPROVED);
    }

    /**
     * Scope a query to only include rejected submissions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::REJECTED);
    }

    /**
     * Scope a query to only include published submissions.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::PUBLISHED);
    }

    /**
     * Scope a query to only include submissions by teacher.
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope a query to only include submissions by exam.
     */
    public function scopeByExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    /**
     * Scope a query to only include submissions by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope a query to only include submissions by academic year.
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope a query to only include completed submissions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('completion_percentage', '>=', 100);
    }

    /**
     * Scope a query to only include incomplete submissions.
     */
    public function scopeIncomplete($query)
    {
        return $query->where('completion_percentage', '<', 100);
    }

    /**
     * Scope a query to only include submissions needing attention (low completion).
     */
    public function scopeNeedsAttention($query, $threshold = 50)
    {
        return $query->where('completion_percentage', '<', $threshold)
                    ->whereIn('status', [self::DRAFT, self::REJECTED]);
    }

    /**
     * Get submission statistics for dashboard.
     */
    public static function getStatistics($teacherId = null, $academicYearId = null): array
    {
        $query = self::query();
        
        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $total = $query->count();
        $draft = $query->clone()->draft()->count();
        $submitted = $query->clone()->submitted()->count();
        $pending = $query->clone()->pending()->count();
        $approved = $query->clone()->approved()->count();
        $rejected = $query->clone()->rejected()->count();
        $published = $query->clone()->published()->count();

        $completed = $query->clone()->completed()->count();
        $incomplete = $query->clone()->incomplete()->count();

        return [
            'total' => $total,
            'draft' => $draft,
            'submitted' => $submitted,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'published' => $published,
            'completed' => $completed,
            'incomplete' => $incomplete,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'approval_rate' => ($submitted + $pending) > 0 ? round(($approved / ($submitted + $pending)) * 100, 2) : 0,
        ];
    }

    /**
     * Find or create submission for exam, class, and teacher.
     */
    public static function findOrCreate($examId, $classId, $teacherId, $academicYearId = null): self
    {
        $submission = self::where([
            'exam_id' => $examId,
            'class_id' => $classId,
            'teacher_id' => $teacherId,
        ])->when($academicYearId, function ($query) use ($academicYearId) {
            return $query->where('academic_year_id', $academicYearId);
        })->first();

        if (!$submission) {
            $submission = self::create([
                'exam_id' => $examId,
                'class_id' => $classId,
                'teacher_id' => $teacherId,
                'submitted_by' => Auth::id(),
                'academic_year_id' => $academicYearId ?? AcademicYear::current()?->id,
                'status' => self::DRAFT,
                'students_count' => 0,
                'subjects_count' => 0,
                'marks_entered_count' => 0,
                'total_marks_count' => 0,
                'completion_percentage' => 0,
            ]);
        }

        return $submission;
    }

    /**
     * Get formatted submitted date.
     */
    public function getFormattedSubmittedAt(): ?string
    {
        return $this->submitted_at?->format('M j, Y g:i A');
    }

    /**
     * Get formatted approved date.
     */
    public function getFormattedApprovedAt(): ?string
    {
        return $this->approved_at?->format('M j, Y g:i A');
    }

    /**
     * Get formatted rejected date.
     */
    public function getFormattedRejectedAt(): ?string
    {
        return $this->rejected_at?->format('M j, Y g:i A');
    }

    /**
     * Get the teacher's name.
     */
    public function getTeacherName(): string
    {
        return $this->teacher ? $this->teacher->full_name : 'Unknown Teacher';
    }

    /**
     * Get the class name.
     */
    public function getClassName(): string
    {
        return $this->class ? $this->class->name : 'Unknown Class';
    }

    /**
     * Get the exam name.
     */
    public function getExamName(): string
    {
        return $this->exam ? $this->exam->name : 'Unknown Exam';
    }

    /**
     * Get the submitted by user's name.
     */
    public function getSubmittedByName(): string
    {
        return $this->submittedBy ? $this->submittedBy->name : 'Unknown User';
    }

    /**
     * Check if submission has any marks.
     */
    public function hasMarks(): bool
    {
        return $this->examMarks()->exists();
    }

    /**
     * Get the average marks for this submission.
     */
    public function getAverageMarks(): ?float
    {
        return $this->examMarks()->avg('marks_obtained');
    }

    /**
     * Get the highest marks for this submission.
     */
    public function getHighestMarks(): ?float
    {
        return $this->examMarks()->max('marks_obtained');
    }

    /**
     * Get the lowest marks for this submission.
     */
    public function getLowestMarks(): ?float
    {
        return $this->examMarks()->min('marks_obtained');
    }

    /**
     * Get submission progress as a percentage.
     */
    public function getProgressPercentage(): float
    {
        return $this->completion_percentage ?? 0;
    }

    /**
     * Get submission duration in days.
     */
    public function getSubmissionDuration(): ?int
    {
        if (!$this->submitted_at || !$this->created_at) {
            return null;
        }

        return $this->created_at->diffInDays($this->submitted_at);
    }

    /**
     * Get approval duration in days.
     */
    public function getApprovalDuration(): ?int
    {
        if (!$this->submitted_at || !$this->approved_at) {
            return null;
        }

        return $this->submitted_at->diffInDays($this->approved_at);
    }
}