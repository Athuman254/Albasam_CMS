<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAdmission extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'student_admissions';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $casts = ['has_exit_school' => 'boolean'];
    protected $fillable = [
        'date', 'date_of_exit', 'division_id', 'has_exit_school'
    ];

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
