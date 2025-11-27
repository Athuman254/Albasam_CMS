<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\AcademicYear;

class Exam extends Model
{
    //
    const DRAFT = 'draft';
    const ACTIVE = 'active';
    const COMPLETED = 'completed';
    const PUBLISHED = 'published';

    protected $fillable = [
        'name',
        'academic_year_id',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function subjects()
    {
        return $this->hasMany(ExamSubject::class, 'exam_id');
    }

    /**
     * Relationship with skills through exam_skills pivot table
     */
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'exam_skills')
            ->withPivot('weightage')
            ->withTimestamps();
    }

    /**
     * Relationship with subjects through skills
     */
    public function subjectsThroughSkills()
    {
        return $this->hasManyThrough(
            Subject::class,
            Skill::class,
            'id', // Foreign key on skills table
            'id', // Foreign key on subjects table
            'id', // Local key on exams table
            'subject_id' // Local key on skills table
        )->distinct();
    }

    /**
     * Get skills for a specific subject in this exam
     */
    public function skillsForSubject($subjectId)
    {
        return $this->skills()->whereHas('subject', function ($query) use ($subjectId) {
            $query->where('id', $subjectId);
        });
    }

    /**
     * Scope for active exams
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::ACTIVE);
    }

    /**
     * Scope for published exams
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::PUBLISHED);
    }

    /**
     * Scope for draft exams
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::DRAFT);
    }

    /**
     * Check if exam is active
     */
    public function isActive()
    {
        return $this->status === self::ACTIVE;
    }

    /**
     * Check if exam is published
     */
    public function isPublished()
    {
        return $this->status === self::PUBLISHED;
    }

    /**
     * Check if exam is completed
     */
    public function isCompleted()
    {
        return $this->status === self::COMPLETED;
    }
}
