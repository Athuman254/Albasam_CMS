<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class Sibling extends Model
{
    use HasHashid, HashidRouting;

    protected $table = 'siblings';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $guarded = ['id'];
//    protected $fillable = [
//        'student_id', 'name', 'age', 'gender_id', 'current_school', 'current_class'
//    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }
}
