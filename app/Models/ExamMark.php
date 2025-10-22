<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamMark extends Model
{
    const DRAFT = 'draft';
    const REVIEWED = 'reviewed';
    const PUBLISHED = 'published';
    protected $fillable = [
      'exam_subject_id',
      'student_id',
      'marks_obtained',
      'status',
      'remarks'
    ];

    public function examSubject(){
      return $this->belongsTo(ExamSubject::class, 'exam_subject_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function percentage(): ?float
    {
        if (!$this->examSubject || !$this->examSubject->max_marks) {
            return null;
        }

        return round(($this->marks_obtained / $this->examSubject->max_marks) * 100, 2);
    }
}
