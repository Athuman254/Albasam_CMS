<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasHashid, HashidRouting;
    
    protected $table = 'attendance_records';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'teacher_id', 'student_id', 'rank_id', 'date', 'status', 'remarks'
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
    
    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function rank(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Rank::class,'class_id');
    }
    
    public function scopeSearch($query, $terms = ''): void
    {
        collect($terms)->filter()->each(function ($term) use ($query) {

            $term = '%'.$term.'%';

            return $query->whereHas('student', function ($q) use ($term) {
                $q->where('first_name', 'like', $term);
            });
        });
    }
}
