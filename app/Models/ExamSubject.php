<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubject extends Model
{
    protected $fillable = [
        'exam_id',
        'class_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'max_marks'
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Relationship with subject
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relationship with exam
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Relationship with class (Rank model)
     */
    public function class()
    {
        return $this->belongsTo(Rank::class, 'class_id');
    }

    /**
     * Relationship with exam marks
     */
    public function examMarks()
    {
        return $this->hasMany(ExamMark::class);
    }

    /**
     * Relationship with skills through the subject
     */
    public function skills()
    {
        return $this->hasManyThrough(
            Skill::class,
            Subject::class,
            'id', // Foreign key on subjects table
            'subject_id', // Foreign key on skills table
            'subject_id', // Local key on exam_subjects table
            'id' // Local key on subjects table
        );
    }

    /**
     * Relationship with active skills through the subject
     */
    public function activeSkills()
    {
        return $this->hasManyThrough(
            Skill::class,
            Subject::class,
            'id', // Foreign key on subjects table
            'subject_id', // Foreign key on skills table
            'subject_id', // Local key on exam_subjects table
            'id' // Local key on subjects table
        )->where('skills.is_active', true);
    }

    /**
     * Get skills that are assigned to this exam subject
     * Through the exam_skills pivot table
     */
    public function assignedSkills()
    {
        return $this->belongsToMany(Skill::class, 'exam_skills', 'exam_id', 'skill_id')
            ->wherePivot('exam_id', $this->exam_id)
            ->withPivot('weightage')
            ->withTimestamps();
    }

    /**
     * Get exam skills with weightage for this specific subject in the exam
     */
    public function examSkillsWithWeightage()
    {
        return $this->hasManyThrough(
            Skill::class,
            Subject::class,
            'id',
            'subject_id',
            'subject_id',
            'id'
        )->whereHas('exams', function($query) {
            $query->where('exam_id', $this->exam_id);
        })->withPivotValue('exam_skills.weightage');
    }

    /**
     * Get total marks allocated to skills for this exam subject
     */
    public function getSkillBasedMarksTotalAttribute()
    {
        return $this->assignedSkills()->sum('exam_skills.weightage');
    }

    /**
     * Check if this exam subject uses skill-based assessment
     */
    public function getUsesSkillAssessmentAttribute()
    {
        return $this->assignedSkills()->count() > 0;
    }

    /**
     * Scope for exam subjects that use skill-based assessment
     */
    public function scopeUsesSkillAssessment($query)
    {
        return $query->whereHas('assignedSkills');
    }

    /**
     * Scope for exam subjects by specific skill
     */
    public function scopeBySkill($query, $skillId)
    {
        return $query->whereHas('skills', function($query) use ($skillId) {
            $query->where('skills.id', $skillId);
        });
    }

    /**
     * Get available skills that can be assigned to this exam subject
     * (Skills that belong to the subject but aren't yet assigned to this exam)
     */
    public function getAvailableSkillsAttribute()
    {
        $assignedSkillIds = $this->assignedSkills()->pluck('skills.id');
        
        return $this->subject->skills()
            ->whereNotIn('id', $assignedSkillIds)
            ->active()
            ->get();
    }

    /**
     * Assign a skill to this exam subject with weightage
     */
    public function assignSkill($skillId, $weightage = 0)
    {
        // Verify the skill belongs to the subject
        $skill = Skill::where('id', $skillId)
            ->where('subject_id', $this->subject_id)
            ->firstOrFail();

        // Attach to exam with weightage
        $this->exam->skills()->attach($skillId, [
            'weightage' => $weightage
        ]);

        return $this;
    }

    /**
     * Remove a skill from this exam subject
     */
    public function removeSkill($skillId)
    {
        $this->exam->skills()->detach($skillId);
        return $this;
    }

    /**
     * Update weightage for a skill in this exam subject
     */
    public function updateSkillWeightage($skillId, $weightage)
    {
        $this->exam->skills()->updateExistingPivot($skillId, [
            'weightage' => $weightage
        ]);
        
        return $this;
    }

    /**
     * Get the duration of the exam in minutes
     */
    public function getDurationAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }
        return null;
    }

    /**
     * Check if the exam subject is scheduled for today
     */
    public function getIsTodayAttribute()
    {
        return $this->exam_date?->isToday();
    }

    /**
     * Check if the exam subject is upcoming
     */
    public function getIsUpcomingAttribute()
    {
        return $this->exam_date?->isFuture();
    }

    /**
     * Check if the exam subject is completed
     */
    public function getIsCompletedAttribute()
    {
        return $this->exam_date?->isPast();
    }
}