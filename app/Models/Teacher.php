<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'teachers';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'user_id', 'employee_id', 'specialization_area_id', 'teacher_title_id', 'tsc_number', 'years_of_experience',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class, 'specialization_area_id', 'id');
    }

    public function title(): BelongsTo
    {
        return $this->belongsTo(TeacherTitle::class, 'teacher_title_id', 'id');
    }
}
