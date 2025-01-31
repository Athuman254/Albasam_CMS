<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkHistory extends Model
{
    use HasHashid, HashidRouting;

    protected $table = 'work_histories';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'employee_id', 'institution_name', 'start_date', 'end_date', 'year_of_completion'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
