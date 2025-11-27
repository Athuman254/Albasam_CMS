<?php

namespace App\Models\Timetable;

use App\Models\Settings\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableTeacherWorkload extends Model
{
    use HasFactory;

    protected $table = 'timetable_teacher_workload';

    protected $fillable = [
        'academic_year_id',
        'teacher_id',
        'total_classes',
        'total_subjects',
        'total_hours_per_week',
        'classes_per_day_avg',
        'last_calculated_at',
    ];

    protected $casts = [
        'total_classes' => 'integer',
        'total_subjects' => 'integer',
        'total_hours_per_week' => 'integer',
        'classes_per_day_avg' => 'decimal:2',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Get the academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the teacher.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Check if teacher is overloaded based on config limits.
     */
    public function isOverloaded(): bool
    {
        $limits = config('timetable.limits');

        return $this->total_classes > $limits['max_classes_per_teacher']
            || $this->total_subjects > $limits['max_subjects_per_teacher']
            || $this->total_hours_per_week > $limits['max_hours_per_week'];
    }

    /**
     * Get workload percentage based on max hours.
     */
    public function getWorkloadPercentageAttribute(): float
    {
        $maxHours = config('timetable.limits.max_hours_per_week');
        return round(($this->total_hours_per_week / $maxHours) * 100, 2);
    }

    /**
     * Get remaining capacity.
     */
    public function getRemainingCapacityAttribute(): array
    {
        $limits = config('timetable.limits');

        return [
            'classes' => max(0, $limits['max_classes_per_teacher'] - $this->total_classes),
            'subjects' => max(0, $limits['max_subjects_per_teacher'] - $this->total_subjects),
            'hours' => max(0, $limits['max_hours_per_week'] - $this->total_hours_per_week),
        ];
    }
}
