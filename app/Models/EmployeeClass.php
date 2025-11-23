<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use app\models\Settings\AcademicYear;

class EmployeeClass extends Model
{
    use HasFactory;

    protected $table = 'employee_class';

    protected $fillable = [
        'employee_id',
        'teacher_id', // Added teacher_id to fillable
        'class_id',
        'subject_id',
        'academic_year_id',
        'is_class_teacher',
        'notes'
    ];

    protected $casts = [
        'is_class_teacher' => 'boolean',
    ];

    /**
     * Get the employee that owns the assignment.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the teacher that owns the assignment.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }

    /**
     * Get the class that owns the assignment.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'class_id');
    }

    /**
     * Get the subject that owns the assignment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the academic year that owns the assignment.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Boot method to automatically set teacher_id if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employeeClass) {
            // Automatically set teacher_id to employee_id if not provided
            if (empty($employeeClass->teacher_id) && !empty($employeeClass->employee_id)) {
                $employeeClass->teacher_id = $employeeClass->employee_id;
            }
        });

        static::updating(function ($employeeClass) {
            // Ensure teacher_id is always set when updating
            if (empty($employeeClass->teacher_id) && !empty($employeeClass->employee_id)) {
                $employeeClass->teacher_id = $employeeClass->employee_id;
            }
        });
    }
}