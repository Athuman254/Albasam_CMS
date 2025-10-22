<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubject extends Model
{
   protected $fillable =[
      'exam_id',
      'class_id',
      'subject_id',
      'exam_date',
      'start_time',
      'end_time',
      'max_marks'
   ];

   public function subject(){
      return $this->belongsTo(Subject::class);
   }
}
