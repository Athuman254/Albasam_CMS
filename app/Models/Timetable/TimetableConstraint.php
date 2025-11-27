<?php

namespace App\Models\Timetable;

use App\Models\Settings\AcademicYear;
use App\Models\Rank;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableConstraint extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'constraint_type',
        'teacher_id',
        'class_id',
        'subject_id',
        'room_id',
        'day_of_week',
        'period_number',
        'constraint_value',
        'notes',
    ];

    protected $casts = [
        'period_number' => 'integer',
    ];

    /**
     * Get the academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the teacher (if applicable).
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the class (if applicable).
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'class_id');
    }

    /**
     * Get the subject (if applicable).
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the room (if applicable).
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(TimetableRoom::class);
    }

    /**
     * Scope to get constraints for a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('constraint_type', $type);
    }

    /**
     * Scope to get unavailable constraints.
     */
    public function scopeUnavailable($query)
    {
        return $query->where('constraint_value', 'unavailable');
    }

    /**
     * Scope to get preferred constraints.
     */
    public function scopePreferred($query)
    {
        return $query->where('constraint_value', 'preferred');
    }
}
