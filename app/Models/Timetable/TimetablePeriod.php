<?php

namespace App\Models\Timetable;

use App\Models\Settings\AcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetablePeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'period_name',
        'day_of_week',
        'start_time',
        'end_time',
        'is_break',
        'break_type',
        'duration_minutes',
        'period_order',
        'status',
    ];

    protected $casts = [
        'is_break' => 'boolean',
        'duration_minutes' => 'integer',
        'period_order' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Get the academic year that owns the period.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the allocations for this period.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(TimetableAllocation::class, 'period_id');
    }

    /**
     * Scope to get only active periods.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get periods for a specific day.
     */
    public function scopeForDay($query, string $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope to get only teaching periods (not breaks).
     */
    public function scopeTeachingPeriods($query)
    {
        return $query->where('is_break', false);
    }

    /**
     * Scope to get only break periods.
     */
    public function scopeBreakPeriods($query)
    {
        return $query->where('is_break', true);
    }

    /**
     * Get formatted time slot.
     */
    public function getTimeSlotAttribute(): string
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
