<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'guardians';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'student_id', 'relationship_id', 'first_name', 'middle_name', 'last_name',
        'email', 'phone', 'identification_number', 'profession'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function relationship()
    {
        return $this->belongsTo(Relationship::class, 'relationship_id', 'id');
    }
}
