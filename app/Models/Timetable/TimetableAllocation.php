<?php

namespace App\Models\Timetable;

use App\Models\Settings\AcademicYear;
use App\Models\Rank;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_id',
        'period_id',
        'teacher_id',
        'subject_id',
        'class_id',
        'room_id',
        'academic_year_id',
        'day_of_week',
        'period_number',
        'start_time',
        'end_time',
        'status',
        'is_substitution',
        'original_teacher_id',
        'notes',
    ];

    protected $casts = [
        'period_number' => 'integer',
        'is_substitution' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get the version.
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(TimetableVersion::class);
    }

    /**
     * Get the period.
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(TimetablePeriod::class);
    }

    /**
     * Get the teacher.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'class_id');
    }

    /**
     * Get the room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(TimetableRoom::class);
    }

    /**
     * Get the academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the original teacher (if substituted).
     */
    public function originalTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_teacher_id');
    }

    /**
     * Scope to get allocations for a specific class.
     */
    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to get allocations for a specific teacher.
     */
    public function scopeForTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope to get allocations for a specific day.
     */
    public function scopeForDay($query, string $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope to get allocations for a specific room.
     */
    public function scopeForRoom($query, int $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Scope to get only scheduled allocations.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Get formatted time slot.
     */
    public function getTimeSlotAttribute(): string
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
