<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryGrade extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;
    
    protected $table = 'salary_grades';
    protected $primaryKey = 'id';
    protected $casts = ['activated' => 'boolean'];
    protected $appends = ['hashid'];
    protected $fillable = ['name', 'activated'];
    
    public function scopeActivated($query): void
    {
        $query->where('activated', '=', true);
    }
}
