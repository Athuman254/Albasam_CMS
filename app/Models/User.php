<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    use Notifiable, HasHashid, HashidRouting, HasRolesAndPermissions;

    protected $connection = 'mysql';

    protected $table = 'users';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'hashid'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'branch_id',
        'password',
        'email_verified_at',
        'activated',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activated' => 'boolean',
            'is_admin' => 'boolean',
            'is_teacher' => 'boolean',
            'is_parent' => 'boolean',
        ];
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }


    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Relationship with subjects (for teachers)
     * Get subjects that this teacher is qualified to teach
     */
    public function subjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Subject::class,
            'teacher_qualifications',
            'teacher_id',
            'subject_id'
        )->withTimestamps();
    }

    /**
     * Alias for subjects relationship to be more semantic
     */
    public function qualifications(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->subjects();
    }

    /**
     * Relationship with student
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Relationship with employee
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }


    public function scopeActivated($query): void
    {
        $query->where('activated', '=', true);
    }

    public function scopeAdmin($query): void
    {
        $query->where('is_admin', '=', true);
    }

    public function scopeTeacher($query): void
    {
        $query->where('is_teacher', '=', true);
    }

    public function scopeParent($query): void
    {
        $query->where('is_parent', '=', true);
    }

    /**
     * Scope for users who are students
     */
    public function scopeStudent($query): void
    {
        $query->whereHas('student');
    }

    /**
     * Scope for users with student role
     */
    public function scopeHasStudent($query): void
    {
        $query->whereHas('student');
    }

    /**
     * Check if user is a student
     */
    public function getIsStudentAttribute(): bool
    {
        return $this->student()->exists();
    }

    /**
     * Get student data if user is a student
     */
    public function getStudentDataAttribute()
    {
        return $this->student;
    }

    /**
     * Get the student's admission number (if user is a student)
     */
    public function getStudentAdmissionNumberAttribute(): ?string
    {
        return $this->student?->admission_number;
    }

    /**
     * Get the student's full name (if user is a student)
     */
    public function getStudentFullNameAttribute(): ?string
    {
        return $this->student?->full_name;
    }

    /**
     * Get the student's class/rank (if user is a student)
     */
    public function getStudentClassAttribute(): ?string
    {
        return $this->student?->rank?->name;
    }

    /**
     * Check if user is a teacher
     */
    public function isTeacher(): bool
    {
        return $this->is_teacher || $this->hasRole('teacher') || $this->teacher()->exists();
    }
}
