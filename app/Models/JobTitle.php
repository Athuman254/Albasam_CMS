<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobTitle extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;
    
    protected $table = 'job_titles';
    protected $primaryKey = 'id';
    protected $casts = ['activated' => 'boolean'];
    protected $appends = ['hashid'];
    protected $fillable = [
        'title', 'salary_scale_id', 'salary_grade_id', 'activated'
    ];
    
    public function scale(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SalaryScale::class, 'salary_scale_id', 'id');
    }
    
    public function grade(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SalaryGrade::class, 'salary_grade_id', 'id');
    }
    
    public function scopeActivated($query): void
    {
        $query->where('activated', '=', true);
    }
}
