<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubjectSkill extends Model
{
    protected $fillable = ['exam_subject_id', 'skill_name', 'max_marks', 'order'];
    
    public function examSubject()
    {
        return $this->belongsTo(ExamSubject::class);
    }
    
    public function skillMarks()
    {
        return $this->hasMany(ExamSkillMark::class);
    }
}