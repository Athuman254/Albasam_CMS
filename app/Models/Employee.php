<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Settings\AcademicYear;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasHashid, HashidRouting, HasFactory;

    protected $guard = 'employee';

    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $appends = [
        'hashid',
        'full_name',
        'formal_name',
        'assigned_classes_count',
        'assigned_subjects_count',
        'current_assignments_count',
        'is_teacher'
    ];

    protected $casts = [
        'use_existing_user' => 'bool',
        'has_system_access' => 'bool',
        'in_payroll' => 'bool',
        'pays_paye' => 'bool',
        'pays_sha' => 'bool',
        'pays_nssf' => 'bool',
        'pays_housing_levy' => 'bool',
        'date_of_hire' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'use_existing_user',
        'user_id',
        'employment_type_id',
        'employment_status_id',
        'honorific_id',
        'marital_status_id',
        'gender_id',
        'religion_id',
        'staff_number',
        'date_of_hire',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'primary_phone',
        'secondary_phone',
        'permanent_physical_address',
        'secondary_physical_address',
        'postal_address',
        'identification_number',
        'tax_identification_pin',
        'has_system_access',
        'password',
        'in_payroll',
        'pays_paye',
        'pays_sha',
        'sha_no',
        'pays_nssf',
        'nssf_no',
        'pays_housing_levy',
        'username',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Disable remember token for employees
     */
    public function getRememberTokenName()
    {
        return null;
    }

    /**
     * Get the user associated with the employee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the employment type of the employee (camelCase)
     */
    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class, 'employment_type_id', 'id');
    }

    /**
     * Snake case alias for employmentType
     */
    public function employment_type(): BelongsTo
    {
        return $this->employmentType();
    }

    /**
     * Get the employment status of the employee (camelCase)
     */
    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(EmploymentStatus::class, 'employment_status_id', 'id');
    }

    /**
     * Snake case alias for employmentStatus
     */
    public function employment_status(): BelongsTo
    {
        return $this->employmentStatus();
    }

    /**
     * Get the honorific of the employee
     */
    public function honorific(): BelongsTo
    {
        return $this->belongsTo(Honorific::class, 'honorific_id', 'id');
    }

    /**
     * Get the marital status of the employee (camelCase)
     */
    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class, 'marital_status_id', 'id');
    }

    /**
     * Snake case alias for maritalStatus
     */
    public function marital_status(): BelongsTo
    {
        return $this->maritalStatus();
    }

    /**
     * Get the gender of the employee
     */
    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    /**
     * Get the religion of the employee
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class, 'religion_id', 'id');
    }

    /**
     * Get the teacher record associated with the employee
     */
    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Get the emergency contacts of the employee
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Get the qualifications of the employee
     */
    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class);
    }

    /**
     * Get the work histories of the employee (camelCase)
     */
    public function workHistories(): HasMany
    {
        return $this->hasMany(WorkHistory::class);
    }

    /**
     * Snake case alias for workHistories
     */
    public function work_histories(): HasMany
    {
        return $this->workHistories();
    }

    /**
     * Get the classes assigned to this employee (teacher)
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Rank::class, 'employee_class', 'employee_id', 'class_id')
            ->withPivot('id', 'subject_id', 'academic_year_id', 'is_class_teacher', 'notes', 'created_at', 'updated_at')
            ->withTimestamps();
    }

    /**
     * Get the subjects taught by this employee
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'employee_class', 'employee_id', 'subject_id')
            ->withPivot('id', 'class_id', 'academic_year_id', 'is_class_teacher', 'notes', 'created_at', 'updated_at')
            ->withTimestamps();
    }

    /**
     * Get the employee class assignments (camelCase)
     */
    public function employeeClasses(): HasMany
    {
        return $this->hasMany(EmployeeClass::class, 'employee_id');
    }

    /**
     * Snake case alias for employeeClasses
     */
    public function employee_classes(): HasMany
    {
        return $this->employeeClasses();
    }

    /**
     * Get assigned subjects with pivot data
     */
    public function assignedSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'employee_id', 'subject_id')
            ->withPivot('academic_year_id', 'class_id')
            ->withTimestamps();
    }

    /**
     * Get exam marks submitted by this employee
     */
    public function submittedMarks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'submitted_by');
    }

    /**
     * Get exam submissions by this employee
     */
    public function examSubmissions(): HasMany
    {
        return $this->hasMany(ExamSubmission::class, 'teacher_id');
    }

    /**
     * Get the employee incomes
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(EmployeeIncome::class);
    }

    /**
     * Get the employee deductions
     */
    public function deductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    /**
     * Get the basic salary of the employee
     */
    public function basicSalary(): HasOne
    {
        return $this->hasOne(EmployeeIncome::class)->whereHas('income', function ($q) {
            return $q->where('name', 'like', '%basic%');
        });
    }

    /**
     * Get the staff attendances for the employee
     */
    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }


    /**
     * Get current academic year assignments
     */
    public function currentAssignments()
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return collect();
        }

        return $this->employeeClasses()
            ->with(['class.stream', 'subject', 'academicYear'])
            ->where('academic_year_id', $currentAcademicYear->id)
            ->get();
    }

    /**
     * Get class teacher assignments
     */
    public function classTeacherAssignments()
    {
        return $this->employeeClasses()
            ->with(['class.stream', 'academicYear'])
            ->where('is_class_teacher', true)
            ->get();
    }

    /**
     * Get subject assignments
     */
    public function subjectAssignments()
    {
        return $this->employeeClasses()
            ->with(['class.stream', 'subject', 'academicYear'])
            ->whereNotNull('subject_id')
            ->get();
    }

    /**
     * Generate staff number
     */
    public static function generateStaffNumber(): string
    {
        $lastEmployee = Employee::withTrashed()->orderBy('id', 'desc')->first();
        $prefix = 'EMP-';
        $month = now()->format('m');
        $year = now()->format('y');

        if ($lastEmployee) {
            $lastCode = $lastEmployee->staff_number;
            // Extract number from EMP-0011025 format
            if (preg_match('/EMP-(\d+)/', $lastCode, $matches)) {
                $lastNumber = intval($matches[1]);
                $nextNumber = str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
            } else {
                $nextNumber = '0000001';
            }
        } else {
            $nextNumber = '0000001';
        }

        return "{$prefix}{$nextNumber}";
    }

    /**
     * Search scope for employees
     */
    public function scopeSearch(Builder $query, string $terms = null): Builder
    {
        if (!$terms) {
            return $query;
        }

        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%' . $term . '%';

            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('middle_name', 'like', $term)
                    ->orWhere('staff_number', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('primary_phone', 'like', $term)
                    ->orWhere('identification_number', 'like', $term)
                    ->orWhereHas('employmentType', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    })
                    ->orWhereHas('employmentStatus', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    });
            });
        });

        return $query;
    }

    /**
     * Scope for active employees (those with system access)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('has_system_access', true);
    }

    /**
     * Scope for teachers (employees with class or subject assignments)
     */
    public function scopeTeachers(Builder $query): Builder
    {
        return $query->whereHas('employeeClasses');
    }

    /**
     * Scope for subject teachers (employees assigned to subjects)
     */
    public function scopeSubjectTeachers(Builder $query): Builder
    {
        return $query->whereHas('employeeClasses', function ($q) {
            $q->whereNotNull('subject_id');
        });
    }

    /**
     * Scope for class teachers
     */
    public function scopeClassTeachers(Builder $query): Builder
    {
        return $query->whereHas('employeeClasses', function ($q) {
            $q->where('is_class_teacher', true);
        });
    }

    /**
     * Scope for employees in payroll
     */
    public function scopeInPayroll(Builder $query): Builder
    {
        return $query->where('in_payroll', true);
    }

    /**
     * Scope for employees by employment type
     */
    public function scopeByEmploymentType(Builder $query, $employmentTypeId): Builder
    {
        return $query->where('employment_type_id', $employmentTypeId);
    }

    /**
     * Scope for employees by employment status
     */
    public function scopeByEmploymentStatus(Builder $query, $employmentStatusId): Builder
    {
        return $query->where('employment_status_id', $employmentStatusId);
    }

    /**
     * Scope to include assignments count
     */
    public function scopeWithAssignmentsCount(Builder $query): Builder
    {
        return $query->withCount(['employeeClasses as class_assignments_count', 'employeeClasses as subject_assignments_count']);
    }

    /**
     * Scope to include current assignments
     */
    public function scopeWithCurrentAssignments(Builder $query): Builder
    {
        return $query->with(['employeeClasses' => function ($q) {
            $currentAcademicYear = AcademicYear::where('is_active', true)->first();
            if ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear->id);
            }
            $q->with(['class.stream', 'subject', 'academicYear']);
        }]);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        $names = [$this->first_name];

        if ($this->middle_name) {
            $names[] = $this->middle_name;
        }

        $names[] = $this->last_name;

        return implode(' ', array_filter($names));
    }

    /**
     * Get formal name with honorific
     */
    public function getFormalNameAttribute(): string
    {
        $honorific = $this->honorific ? $this->honorific->name . ' ' : '';
        return $honorific . $this->full_name;
    }

    /**
     * Get assigned classes count attribute
     */
    public function getAssignedClassesCountAttribute(): int
    {
        if (array_key_exists('class_assignments_count', $this->attributes)) {
            return $this->attributes['class_assignments_count'];
        }

        return $this->employeeClasses()->count();
    }

    /**
     * Get assigned subjects count attribute
     */
    public function getAssignedSubjectsCountAttribute(): int
    {
        if (array_key_exists('subject_assignments_count', $this->attributes)) {
            return $this->attributes['subject_assignments_count'];
        }

        return $this->employeeClasses()->whereNotNull('subject_id')->count();
    }

    /**
     * Get current assignments count attribute
     */
    public function getCurrentAssignmentsCountAttribute(): int
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return 0;
        }

        return $this->employeeClasses()
            ->where('academic_year_id', $currentAcademicYear->id)
            ->count();
    }

    /**
     * Get is teacher attribute
     */
    public function getIsTeacherAttribute(): bool
    {
        return $this->employeeClasses()->exists();
    }

    /**
     * Check if employee is a teacher
     */
    public function isTeacher(): bool
    {
        return $this->employeeClasses()->exists();
    }

    /**
     * Check if employee is a subject teacher
     */
    public function isSubjectTeacher(): bool
    {
        return $this->employeeClasses()->whereNotNull('subject_id')->exists();
    }

    /**
     * Check if employee is a class teacher
     */
    public function isClassTeacher(): bool
    {
        return $this->employeeClasses()->where('is_class_teacher', true)->exists();
    }

    /**
     * Get assigned classes for specific academic year
     */
    public function getClassesForAcademicYear($academicYearId)
    {
        return $this->employeeClasses()
            ->with(['class.stream', 'academicYear'])
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->pluck('class')
            ->filter();
    }

    /**
     * Get assigned subjects for specific academic year
     */
    public function getSubjectsForAcademicYear($academicYearId)
    {
        return $this->employeeClasses()
            ->with(['subject', 'academicYear'])
            ->where('academic_year_id', $academicYearId)
            ->whereNotNull('subject_id')
            ->get()
            ->pluck('subject')
            ->filter();
    }

    /**
     * Get class teacher assignments for academic year
     */
    public function getClassTeacherAssignments($academicYearId = null)
    {
        $query = $this->employeeClasses()
            ->with(['class.stream', 'academicYear'])
            ->where('is_class_teacher', true);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->get();
    }

    /**
     * Get subject assignments for specific class
     */
    public function getSubjectAssignmentsForClass($classId, $academicYearId = null)
    {
        $query = $this->employeeClasses()
            ->with(['subject', 'academicYear'])
            ->where('class_id', $classId)
            ->whereNotNull('subject_id');

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->get();
    }

    /**
     * Check if employee is class teacher for specific class
     */
    public function isClassTeacherForClass($classId, $academicYearId = null): bool
    {
        $query = $this->employeeClasses()
            ->where('is_class_teacher', true)
            ->where('class_id', $classId);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->exists();
    }

    /**
     * Check if employee teaches specific subject
     */
    public function teachesSubject($subjectId, $academicYearId = null): bool
    {
        $query = $this->employeeClasses()
            ->where('subject_id', $subjectId);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->exists();
    }

    /**
     * Assign class to employee using EmployeeClass model
     */
    public function assignClass($classId, $subjectId = null, $academicYearId = null, $isClassTeacher = false, $notes = null): EmployeeClass
    {
        if (!$academicYearId) {
            $academicYear = AcademicYear::where('is_active', true)->first();
            $academicYearId = $academicYear ? $academicYear->id : null;
        }

        if (!$academicYearId) {
            throw new \Exception('No academic year specified and no active academic year found.');
        }

        // Check if assignment already exists
        $existingAssignment = EmployeeClass::where([
            'employee_id' => $this->id,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'academic_year_id' => $academicYearId,
        ])->first();

        if ($existingAssignment) {
            throw new \Exception('This assignment already exists.');
        }

        // If setting as class teacher, remove other class teachers for this class
        if ($isClassTeacher) {
            EmployeeClass::where('class_id', $classId)
                ->where('academic_year_id', $academicYearId)
                ->where('is_class_teacher', true)
                ->update(['is_class_teacher' => false]);
        }

        return EmployeeClass::create([
            'employee_id' => $this->id,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'academic_year_id' => $academicYearId,
            'is_class_teacher' => $isClassTeacher,
            'notes' => $notes,
        ]);
    }

    /**
     * Remove class assignment from employee
     */
    public function removeClassAssignment($classId, $academicYearId = null): bool
    {
        $query = $this->employeeClasses()->where('class_id', $classId);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->delete() > 0;
    }

    /**
     * Remove subject assignment from employee
     */
    public function removeSubjectAssignment($subjectId, $academicYearId = null): bool
    {
        $query = $this->employeeClasses()->where('subject_id', $subjectId);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->delete() > 0;
    }

    /**
     * Remove assignment by ID
     */
    public function removeAssignment($assignmentId): bool
    {
        return $this->employeeClasses()->where('id', $assignmentId)->delete() > 0;
    }

    /**
     * Get all assignments for employee
     */
    public function getAllAssignments($academicYearId = null)
    {
        $query = $this->employeeClasses()->with(['class.stream', 'subject', 'academicYear']);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->get();
    }

    /**
     * Check if employee has any teaching assignments
     */
    public function hasTeachingAssignments($academicYearId = null): bool
    {
        $query = $this->employeeClasses();

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->exists();
    }

    /**
     * Get teaching workload summary
     */
    public function getTeachingWorkload($academicYearId = null): array
    {
        $query = $this->employeeClasses();

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $assignments = $query->get();

        $classTeacherCount = $assignments->where('is_class_teacher', true)->count();
        $subjectCount = $assignments->whereNotNull('subject_id')->count();
        $uniqueClasses = $assignments->pluck('class_id')->unique()->count();
        $uniqueSubjects = $assignments->whereNotNull('subject_id')->pluck('subject_id')->unique()->count();

        return [
            'total_assignments' => $assignments->count(),
            'class_teacher_assignments' => $classTeacherCount,
            'subject_assignments' => $subjectCount,
            'unique_classes' => $uniqueClasses,
            'unique_subjects' => $uniqueSubjects,
        ];
    }

    /**
     * Get employee statistics
     */
    public function getEmployeeStatistics(): array
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $currentAssignments = $currentAcademicYear ?
            $this->employeeClasses()->where('academic_year_id', $currentAcademicYear->id)->count() : 0;

        return [
            'total_assignments' => $this->employeeClasses()->count(),
            'current_assignments' => $currentAssignments,
            'class_teacher_roles' => $this->employeeClasses()->where('is_class_teacher', true)->count(),
            'subject_assignments' => $this->employeeClasses()->whereNotNull('subject_id')->count(),
            'is_active' => $this->has_system_access,
            'in_payroll' => $this->in_payroll,
        ];
    }

    /**
     * Grant system access to employee
     */
    public function grantSystemAccess($password = null): bool
    {
        $updateData = ['has_system_access' => true];

        if ($password) {
            // Use plain text password - the mutator will handle hashing
            $updateData['password'] = $password;
        }

        return $this->update($updateData);
    }

    /**
     * Revoke system access from employee
     */
    public function revokeSystemAccess(): bool
    {
        return $this->update(['has_system_access' => false]);
    }

    /**
     * Update employee password - FIXED: Use plain text, let mutator handle hashing
     */
    public function updatePassword($password): bool
    {
        // Use plain text password - the mutator will handle hashing
        return $this->update(['password' => $password]);
    }

    /**
     * Set password attribute - Only hash if not already hashed
     * This is the SINGLE SOURCE OF TRUTH for password hashing
     */
    public function setPasswordAttribute($value)
    {
        // If value is empty, set to null
        if (empty($value)) {
            $this->attributes['password'] = null;
            return;
        }

        // Only hash the password if it's not already hashed
        // Check if it's already a bcrypt hash (starts with $2y$ and is 60 chars)
        if (!preg_match('/^\$2[ayb]\$.{56}$/', $value)) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            // It's already hashed, use as-is
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Create employee with proper password handling
     */
    public static function createWithPassword(array $attributes)
    {
        // Ensure we're not double-hashing by using the mutator correctly
        return static::create($attributes);
    }

    /**
     * Find employee for authentication
     */
    public function findForPassport($username)
    {
        return $this->where('staff_number', $username)
            ->orWhere('email', $username)
            ->orWhere('primary_phone', $username)
            ->where('has_system_access', true)
            ->first();
    }

    /**
     * Validate password for authentication
     */
    public function validateForPassportPasswordGrant($password)
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Get the unique identifier for authentication
     */
    public function getAuthIdentifierName()
    {
        return 'staff_number';
    }

    /**
     * Get the password for the user
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate staff number when creating new employee
        static::creating(function ($employee) {
            if (empty($employee->staff_number)) {
                $employee->staff_number = static::generateStaffNumber();
            }
        });

        // Set email as username if not provided
        // COMMENTED OUT: username column does not exist in employees table
        // static::creating(function ($employee) {
        //     if (empty($employee->username) && !empty($employee->email)) {
        //         $employee->username = $employee->email;
        //     }
        // });

        // Ensure has_system_access is properly cast
        static::saving(function ($employee) {
            if (is_null($employee->has_system_access)) {
                $employee->has_system_access = false;
            }
        });
    }
}
