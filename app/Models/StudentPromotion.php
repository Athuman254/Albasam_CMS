<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use app\Models\Settings\AcademicYear;

class StudentPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'from_class_id',
        'to_class_id',
        'academic_year_id',
        'promoted_by',
        'special_promotion',
        'reason',
        'has_completed_all_terms',
        'completed_terms',
        'promoted_at'
    ];

    protected $casts = [
        'special_promotion' => 'boolean',
        'has_completed_all_terms' => 'boolean',
        'completed_terms' => 'array',
        'promoted_at' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClass()
    {
        return $this->belongsTo(Rank::class, 'from_class_id');
    }

    public function toClass()
    {
        return $this->belongsTo(Rank::class, 'to_class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function promotedBy()
    {
        return $this->belongsTo(Employee::class, 'promoted_by');
    }
}