<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'skills';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    
    protected $fillable = [
        'name',
        'description',
        'subject_id',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relationship with subject
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relationship with exams
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_skills')
            ->withPivot('weightage')
            ->withTimestamps();
    }

    /**
     * Scope for active skills
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for skills by subject
     */
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Check if skill is used in any exams
     */
    public function isUsedInExams()
    {
        return $this->exams()->count() > 0;
    }

    /**
     * Get the total weightage across all exams
     */
    public function getTotalWeightageAttribute()
    {
        return $this->exams()->sum('exam_skills.weightage');
    }

    /**
     * Activate the skill
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the skill
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}