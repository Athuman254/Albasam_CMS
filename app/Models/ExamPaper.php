<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_subject_id',
        'teacher_id',
        'title',
        'instructions',
        'status',
    ];

    /**
     * Get the exam subject that owns the paper.
     */
    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    /**
     * Get the teacher that set the paper.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
