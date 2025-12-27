<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_file',
        'submission_text',
        'submitted_at',
        'points_earned',
        'teacher_feedback',
        'graded_by',
        'graded_at',
        'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'points_earned' => 'integer',
    ];

    /**
     * Get the assignment that owns the submission.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student that owns the submission.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the teacher who graded the submission.
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Scope for graded submissions.
     */
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    /**
     * Scope for pending submissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope for late submissions.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Check if submission is late.
     */
    public function isLate(): bool
    {
        return $this->submitted_at > $this->assignment->due_date;
    }

    /**
     * Get percentage score.
     */
    public function getPercentageAttribute(): ?float
    {
        if (!$this->points_earned || !$this->assignment->max_points) {
            return null;
        }

        return ($this->points_earned / $this->assignment->max_points) * 100;
    }
}
