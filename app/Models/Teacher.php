<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'teachers';
    protected $primaryKey = 'id';
    protected $appends = ['hashid', 'is_class_teacher'];
    protected $fillable = [
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'honorific_id',
        'job_title_id',
        'specialization_area_id',
        'tsc_number',
        'years_of_experience',
    ];

    /**
     * Get the subjects this teacher is qualified to teach
     */
    public function teachingSubjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Subject::class,
            'teacher_qualifications',
            'teacher_id',
            'subject_id'
        )->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function honorific(): BelongsTo
    {
        return $this->belongsTo(Honorific::class, 'honorific_id', 'id');
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class, 'specialization_area_id', 'id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id', 'id');
    }

    public function rank(): HasOne
    {
        return $this->hasOne(Rank::class, 'teacher_id', 'id');
    }

    /**
     * Get the employee class assignments for this teacher
     */
    public function classAssignments()
    {
        return $this->hasMany(EmployeeClass::class, 'employee_id', 'employee_id');
    }

    /**
     * Get class teacher assignments only
     */
    public function classTeacherAssignments()
    {
        return $this->classAssignments()->where('is_class_teacher', true);
    }

    public function getIsClassTeacherAttribute(): bool
    {
        return $this->classTeacherAssignments()->exists();
    }

    public function scopeIsAClassTeacher($query)
    {
        return $query->whereHas('classTeacherAssignments');
    }

    public function scopeSearch($query, string $terms = null)
    {
        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%' . $term . '%';

            $query->where('first_name', 'like', $term)
                ->orwhere('last_name', 'like', $term)
                ->orWhereHas('employee', function ($q) use ($term) {
                    $q->where('staff_number', 'like', $term);
                });
        });
    }
}
