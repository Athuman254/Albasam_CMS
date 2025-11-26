<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Settings\AcademicYear;

class Rank extends Model
{
    use SoftDeletes, HasHashid, HashidRouting, HasFactory;

    protected $table = 'ranks';
    protected $primaryKey = 'id';
    protected $appends = ['hashid', 'full_name', 'display_name', 'capacity_percentage', 'is_full', 'available_seats', 'class_teacher_name'];

    protected $casts = [
        'activated' => 'bool',
        'is_active' => 'bool',
        'capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'division_id',
        'stream_id',
        'teacher_id',
        'activated',
        'capacity',
        'description',
        'academic_year_id',
        'grade_level',
        'section',
        'room_number',
        'is_active'
    ];

    /**
     * Get the division that owns the rank.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    /**
     * Get the stream that owns the rank.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }

    /**
     * Get the teacher that owns the rank.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    /**
     * Get the academic year that owns the rank.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'id');
    }

    /**
     * Get the subjects for the rank.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'rank_subjects', 'rank_id', 'subject_id')
            ->withTimestamps()
            ->withPivot(['created_at', 'updated_at']);
    }

    /**
     * Get the students for the rank.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'rank_id', 'id');
    }

    /**
     * Get the employee class assignments for this rank.
     */
    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(EmployeeClass::class, 'class_id', 'id');
    }

    /**
     * Get the employees (teachers) assigned to this class.
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_class', 'class_id', 'employee_id')
            ->withPivot('id', 'subject_id', 'academic_year_id', 'is_class_teacher', 'notes', 'created_at', 'updated_at')
            ->withTimestamps();
    }

    /**
     * Get the class teachers for this rank.
     */
    public function classTeachers(): BelongsToMany
    {
        return $this->employees()
            ->wherePivot('is_class_teacher', true);
    }

    /**
     * Get the subject teachers for this rank.
     */
    public function subjectTeachers(): BelongsToMany
    {
        return $this->employees()
            ->wherePivot('is_class_teacher', false)
            ->wherePivotNotNull('subject_id');
    }

    /**
     * Get the full name attribute (with stream).
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->name;

        if ($this->stream) {
            $name .= ' - ' . $this->stream->name;
        }

        if ($this->section) {
            $name .= ' (' . $this->section . ')';
        }

        return $name;
    }

    /**
     * Get the display name attribute (for dropdowns).
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->name;

        if ($this->stream) {
            $name .= ' - ' . $this->stream->name;
        }

        if ($this->section) {
            $name .= ' (' . $this->section . ')';
        }

        return $name;
    }

    /**
     * Get the class capacity percentage.
     */
    public function getCapacityPercentageAttribute(): float
    {
        if (!$this->capacity || $this->capacity == 0) {
            return 0;
        }

        $studentCount = $this->students()->count();
        return round(($studentCount / $this->capacity) * 100, 2);
    }

    /**
     * Check if class is full.
     */
    public function getIsFullAttribute(): bool
    {
        if (!$this->capacity) {
            return false;
        }

        return $this->students()->count() >= $this->capacity;
    }

    /**
     * Get available seats.
     */
    public function getAvailableSeatsAttribute(): int
    {
        if (!$this->capacity) {
            return 0;
        }

        $studentCount = $this->students()->count();
        return max(0, $this->capacity - $studentCount);
    }

    /**
     * Get class teacher name.
     */
    public function getClassTeacherNameAttribute(): string
    {
        // First try to get from employee assignments
        $classTeacher = $this->employeeAssignments()
            ->with('employee')
            ->where('is_class_teacher', true)
            ->first();

        if ($classTeacher && $classTeacher->employee) {
            return $classTeacher->employee->full_name ??
                $classTeacher->employee->first_name . ' ' . $classTeacher->employee->last_name;
        }

        // Fallback to legacy teacher relationship
        if ($this->teacher) {
            return $this->teacher->full_name ?? $this->teacher->name ?? 'Unknown';
        }

        return 'Not Assigned';
    }

    /**
     * Get class statistics - SAFE VERSION
     */
    public function getClassStatisticsAttribute(): array
    {
        try {
            $studentCount = $this->students()->count();

            // Count male and female students - using the gender relationship correctly
            $maleCount = $this->students()->whereHas('gender', function ($query) {
                $query->where('name', 'like', '%male%');
            })->count();

            $femaleCount = $this->students()->whereHas('gender', function ($query) {
                $query->where('name', 'like', '%female%');
            })->count();

            $subjectCount = $this->subjects()->count();
            $teacherCount = $this->employeeAssignments()->distinct('employee_id')->count('employee_id');

            return [
                'total_students' => $studentCount,
                'male_students' => $maleCount,
                'female_students' => $femaleCount,
                'subject_count' => $subjectCount,
                'teacher_count' => $teacherCount,
                'capacity_percentage' => $this->capacity_percentage,
                'available_seats' => $this->available_seats,
                'is_full' => $this->is_full,
            ];
        } catch (\Exception $e) {
            // Return safe default values if there's any error
            \Log::error('Error getting class statistics for rank ' . $this->id . ': ' . $e->getMessage());

            return [
                'total_students' => 0,
                'male_students' => 0,
                'female_students' => 0,
                'subject_count' => 0,
                'teacher_count' => 0,
                'capacity_percentage' => 0,
                'available_seats' => $this->capacity ?? 0,
                'is_full' => false,
            ];
        }
    }

    /**
     * Get current academic year assignments.
     */
    public function getCurrentAssignmentsAttribute()
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return collect();
        }

        return $this->employeeAssignments()
            ->with(['employee', 'subject', 'academicYear'])
            ->where('academic_year_id', $currentAcademicYear->id)
            ->get();
    }

    /**
     * Scope a query to only include activated ranks.
     */
    public function scopeActivated($query): void
    {
        $query->where('activated', true);
    }

    /**
     * Scope a query to only include active ranks.
     */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include ranks with available seats.
     */
    public function scopeWithAvailableSeats($query): void
    {
        $query->where(function ($q) {
            $q->whereNull('capacity')
                ->orWhere(function ($q2) {
                    $q2->whereNotNull('capacity')
                        ->whereRaw('capacity > (SELECT COUNT(*) FROM students WHERE students.rank_id = ranks.id AND students.deleted_at IS NULL)');
                });
        });
    }

    /**
     * Scope a query to only include ranks by division.
     */
    public function scopeByDivision($query, $divisionId): void
    {
        $query->where('division_id', $divisionId);
    }

    /**
     * Scope a query to only include ranks by stream.
     */
    public function scopeByStream($query, $streamId): void
    {
        $query->where('stream_id', $streamId);
    }

    /**
     * Scope a query to only include ranks by academic year.
     */
    public function scopeByAcademicYear($query, $academicYearId): void
    {
        $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope a query to only include ranks by grade level.
     */
    public function scopeByGradeLevel($query, $gradeLevel): void
    {
        $query->where('grade_level', $gradeLevel);
    }

    /**
     * Scope a query to only include ranks with class teacher.
     */
    public function scopeWithClassTeacher($query): void
    {
        $query->whereHas('employeeAssignments', function ($q) {
            $q->where('is_class_teacher', true);
        });
    }

    /**
     * Scope a query to only include ranks without class teacher.
     */
    public function scopeWithoutClassTeacher($query): void
    {
        $query->whereDoesntHave('employeeAssignments', function ($q) {
            $q->where('is_class_teacher', true);
        });
    }

    /**
     * Scope a query to include relationships for class assignments.
     */
    public function scopeWithAssignments($query): void
    {
        $query->with(['employeeAssignments.employee', 'employeeAssignments.subject', 'employeeAssignments.academicYear']);
    }

    /**
     * Search scope for ranks.
     */
    public function scopeSearch($query, string $terms = null)
    {
        if (!$terms) {
            return $query;
        }

        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%' . $term . '%';

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('section', 'like', $term)
                    ->orWhere('room_number', 'like', $term)
                    ->orWhere('grade_level', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhereHas('division', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    })
                    ->orWhereHas('stream', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    })
                    ->orWhereHas('teacher', function ($q) use ($term) {
                        $q->where('name', 'like', $term)
                            ->orWhere('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term);
                    })
                    ->orWhereHas('employees', function ($q) use ($term) {
                        $q->where('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere('staff_number', 'like', $term);
                    });
            });
        });
    }

    /**
     * Get ranks with their student count.
     */
    public function scopeWithStudentCount($query)
    {
        return $query->withCount('students');
    }

    /**
     * Get ranks with their subject count.
     */
    public function scopeWithSubjectCount($query)
    {
        return $query->withCount('subjects');
    }

    /**
     * Get ranks with teacher assignments count.
     */
    public function scopeWithTeacherCount($query)
    {
        return $query->withCount('employeeAssignments');
    }

    /**
     * Get ranks with teacher assignments.
     */
    public function scopeWithTeacherAssignments($query)
    {
        return $query->with(['employeeAssignments' => function ($q) {
            $q->with(['employee', 'subject', 'academicYear']);
        }]);
    }

    /**
     * Activate the rank.
     */
    public function activate(): bool
    {
        return $this->update([
            'activated' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Deactivate the rank.
     */
    public function deactivate(): bool
    {
        return $this->update([
            'activated' => false,
            'is_active' => false,
        ]);
    }

    /**
     * Check if rank can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->students()->count() === 0 &&
            $this->employeeAssignments()->count() === 0;
    }

    /**
     * Get similar classes (same grade level and division).
     */
    public function getSimilarClasses()
    {
        return self::where('id', '!=', $this->id)
            ->when($this->grade_level, function ($query) {
                return $query->where('grade_level', $this->grade_level);
            })
            ->when($this->division_id, function ($query) {
                return $query->where('division_id', $this->division_id);
            })
            ->with(['stream', 'teacher'])
            ->get();
    }

    /**
     * Transfer students to another class.
     */
    public function transferStudentsTo(Rank $targetClass, array $studentIds = []): int
    {
        $query = $this->students();

        if (!empty($studentIds)) {
            $query->whereIn('id', $studentIds);
        }

        return $query->update(['rank_id' => $targetClass->id]);
    }

    /**
     * Assign teacher to class.
     */
    public function assignTeacher(Employee $employee, $academicYearId, $isClassTeacher = false, $subjectId = null): EmployeeClass
    {
        // If setting as class teacher, remove other class teachers
        if ($isClassTeacher) {
            EmployeeClass::where('class_id', $this->id)
                ->where('academic_year_id', $academicYearId)
                ->where('is_class_teacher', true)
                ->update(['is_class_teacher' => false]);
        }

        return EmployeeClass::create([
            'employee_id' => $employee->id,
            'class_id' => $this->id,
            'subject_id' => $subjectId,
            'academic_year_id' => $academicYearId,
            'is_class_teacher' => $isClassTeacher,
        ]);
    }

    /**
     * Remove teacher assignment from class.
     */
    public function removeTeacherAssignment(Employee $employee, $academicYearId): bool
    {
        return EmployeeClass::where('class_id', $this->id)
            ->where('employee_id', $employee->id)
            ->where('academic_year_id', $academicYearId)
            ->delete() > 0;
    }

    /**
     * Get current academic year teacher assignments.
     */
    public function getCurrentYearAssignments()
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return collect();
        }

        return $this->employeeAssignments()
            ->with(['employee', 'subject'])
            ->where('academic_year_id', $currentAcademicYear->id)
            ->get();
    }

    /**
     * Check if employee is assigned to this class in current academic year.
     */
    public function isEmployeeAssigned(Employee $employee): bool
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return false;
        }

        return $this->employeeAssignments()
            ->where('employee_id', $employee->id)
            ->where('academic_year_id', $currentAcademicYear->id)
            ->exists();
    }

    /**
     * Get class teacher for specific academic year.
     */
    public function getClassTeacherForYear($academicYearId = null)
    {
        if (!$academicYearId) {
            $academicYearId = AcademicYear::where('is_active', true)->value('id');
        }

        if (!$academicYearId) {
            return null;
        }

        return $this->employeeAssignments()
            ->with('employee')
            ->where('academic_year_id', $academicYearId)
            ->where('is_class_teacher', true)
            ->first();
    }

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($rank) {
            if ($rank->isForceDeleting()) {
                // Handle force delete
                $rank->subjects()->detach();
                $rank->employees()->detach();
            } else {
                // Handle soft delete
                if (!$rank->canBeDeleted()) {
                    throw new \Exception('Cannot delete class with associated records. Please remove students and assignments first.');
                }
            }
        });

        static::created(function ($rank) {
            // Set default academic year if not provided
            // Commented out - academic_year_id column doesn't exist in current schema
            // if (!$rank->academic_year_id) {
            //     $currentAcademicYear = AcademicYear::where('is_active', true)->first();
            //     if ($currentAcademicYear) {
            //         $rank->update(['academic_year_id' => $currentAcademicYear->id]);
            //     }
            // }
        });
    }
}
