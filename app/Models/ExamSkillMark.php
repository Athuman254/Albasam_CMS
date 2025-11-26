<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSkillMark extends Model
{
    protected $fillable = ['exam_mark_id', 'exam_subject_skill_id', 'marks_obtained'];
    
    public function examMark()
    {
        return $this->belongsTo(ExamMark::class);
    }
    
    public function skill()
    {
        return $this->belongsTo(ExamSubjectSkill::class, 'exam_subject_skill_id');
    }
}