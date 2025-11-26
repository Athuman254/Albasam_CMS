<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Qualification extends Model
{
    use HasHashid, HashidRouting;

    protected $table = 'qualifications';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'qualification_type_id', 'employee_id', 'institution_name', 'course_name', 'year_of_completion'
    ];

    public function qualification_type()
    {
        return $this->belongsTo(QualificationType::class, 'qualification_type_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
