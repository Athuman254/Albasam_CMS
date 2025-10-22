<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function academicYear(){
      return $this->belongsTo(AcademicYear::class,'academic_year_id');
    }

    public function subjects(){
      return $this->hasMany(ExamSubject::class, 'exam_id');
    }
}
