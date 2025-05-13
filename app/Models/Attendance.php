<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;
    
    protected $table = "attendance_records";
    protected $guarded = [];

    public function student(){
        return $this->belongsTo(Student::class);
    }
    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }

    public function rank(){
        return $this->belongsTo(Rank::class,'class_id');
    }
    public function scopeSearch($query, $terms = ''){
        collect($terms)->filter()->each(function ($term) use ($query) {

            $term = '%'.$term.'%';

            return $query->whereHas('student', function ($q) use ($term) {
                $q->where('first_name', 'like', $term);
            });
        });
    }
}
