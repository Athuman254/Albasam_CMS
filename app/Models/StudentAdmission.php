<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class StudentAdmission extends Model
{
    use SoftDeletes;

    protected $table = 'student_admissions';
    protected $primaryKey = 'id';

    // Use regular ID for route binding to avoid Hashids dependency
    public function getRouteKeyName()
    {
        return 'id';
    }

    protected $appends = [
        'hashid',
        'formatted_date',
        'formatted_exit_date', 
        'is_active',
        'student_name',
        'admission_number',
        'student_class',
        'division_name',
        'has_student',
        'student_details',
        'summary'
    ];
    
    protected $casts = [
        'has_exit_school' => 'boolean',
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $fillable = [
        'division_id', 
        'has_exit_school'
    ];

    /**
     * Get the student associated with the admission
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'student_admission_id', 'id');
    }

    /**
     * Get the division associated with the admission
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /**
     * Get admission date formatted - Use created_at since 'date' column doesn't exist
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('M d, Y') : 'N/A';
    }

    /**
     * Get exit date formatted - Use deleted_at
     */
    public function getFormattedExitDateAttribute()
    {
        return $this->deleted_at ? $this->deleted_at->format('M d, Y') : 'N/A';
    }

    /**
     * Check if admission is active
     */
    public function getIsActiveAttribute()
    {
        return !$this->has_exit_school && empty($this->deleted_at);
    }

    /**
     * Get student name through relationship
     */
    public function getStudentNameAttribute()
    {
        return $this->student ? $this->student->full_name : 'No Student Assigned';
    }

    /**
     * Get admission number through relationship
     */
    public function getAdmissionNumberAttribute()
    {
        return $this->student ? $this->student->admission_number : 'N/A';
    }

    /**
     * Get class/rank through student relationship
     */
    public function getStudentClassAttribute()
    {
        return $this->student && $this->student->rank ? $this->student->rank->name : 'N/A';
    }

    /**
     * Get student details for easy access
     */
    public function getStudentDetailsAttribute()
    {
        if (!$this->student) {
            return null;
        }

        return [
            'id' => $this->student->id,
            'full_name' => $this->student->full_name,
            'admission_number' => $this->student->admission_number,
            'class' => $this->student->rank ? $this->student->rank->name : 'N/A',
            'gender' => $this->student->gender ? $this->student->gender->name : 'N/A',
            'date_of_birth' => $this->student->formatted_dob,
            'age' => $this->student->age,
        ];
    }

    /**
     * Get division name for easy access
     */
    public function getDivisionNameAttribute()
    {
        return $this->division ? $this->division->name : 'N/A';
    }

    /**
     * Check if admission has a student assigned
     */
    public function getHasStudentAttribute(): bool
    {
        return !is_null($this->student);
    }

    /**
     * Simple hashid implementation - returns regular ID as string for compatibility
     */
    public function getHashidAttribute()
    {
        return (string) $this->id;
    }

    /**
     * Scope for active admissions
     */
    public function scopeActive($query)
    {
        return $query->where('has_exit_school', false)
                    ->whereNull('deleted_at');
    }

    /**
     * Scope for exited students
     */
    public function scopeExited($query)
    {
        return $query->where('has_exit_school', true)
                    ->orWhereNotNull('deleted_at');
    }

    /**
     * Scope for admissions with students
     */
    public function scopeWithStudent($query)
    {
        return $query->whereHas('student');
    }

    /**
     * Scope for admissions without students
     */
    public function scopeWithoutStudent($query)
    {
        return $query->whereDoesntHave('student');
    }

    /**
     * Scope for admissions by division
     */
    public function scopeByDivision($query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    /**
     * Scope for admissions within date range - Use created_at
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for recent admissions (last 30 days) - Use created_at
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for admissions by year - Use created_at
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('created_at', $year);
    }

    /**
     * Get admission statistics
     */
    public static function getStatistics()
    {
        $total = self::count();
        $active = self::active()->count();
        $exited = self::exited()->count();
        $withStudents = self::withStudent()->count();
        $withoutStudents = self::withoutStudent()->count();

        return [
            'total' => $total,
            'active' => $active,
            'exited' => $exited,
            'with_students' => $withStudents,
            'without_students' => $withoutStudents,
            'completion_rate' => $total > 0 ? round(($withStudents / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Assign a student to this admission
     */
    public function assignStudent(Student $student)
    {
        $student->student_admission_id = $this->id;
        return $student->save();
    }

    /**
     * Remove student from this admission
     */
    public function removeStudent()
    {
        if ($this->student) {
            $this->student->student_admission_id = null;
            return $this->student->save();
        }
        return false;
    }

    /**
     * Mark admission as exited
     */
    public function markAsExited($exitDate = null)
    {
        $this->has_exit_school = true;
        if ($exitDate) {
            $this->deleted_at = $exitDate;
        }
        return $this->save();
    }

    /**
     * Reactivate admission
     */
    public function reactivate()
    {
        $this->has_exit_school = false;
        $this->deleted_at = null;
        return $this->save();
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-delete student when admission is deleted (if needed)
        static::deleting(function ($admission) {
            if ($admission->student && config('student.admission.cascade_delete', false)) {
                $admission->student->delete();
            }
        });

        // Restore student when admission is restored
        static::restoring(function ($admission) {
            if ($admission->student) {
                $admission->student->withTrashed()->restore();
            }
        });
    }

    /**
     * Get admission summary for display
     */
    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'hashid' => $this->hashid,
            'date' => $this->formatted_date,
            'student_name' => $this->student_name,
            'admission_number' => $this->admission_number,
            'class' => $this->student_class,
            'division' => $this->division_name,
            'status' => $this->is_active ? 'Active' : 'Exited',
            'exit_date' => $this->formatted_exit_date,
            'has_student' => $this->has_student,
            'created_at' => $this->created_at?->format('M d, Y H:i'),
            'updated_at' => $this->updated_at?->format('M d, Y H:i'),
        ];
    }

    /**
     * Convert the model instance to an array for API responses
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        $array['formatted_date'] = $this->formatted_date;
        $array['formatted_exit_date'] = $this->formatted_exit_date;
        $array['is_active'] = $this->is_active;
        $array['student_name'] = $this->student_name;
        $array['admission_number'] = $this->admission_number;
        $array['student_class'] = $this->student_class;
        $array['division_name'] = $this->division_name;
        $array['has_student'] = $this->has_student;
        $array['student_details'] = $this->student_details;
        $array['summary'] = $this->summary;

        return $array;
    }

    
}