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
    protected $fillable = ['name', 'activated'];

    public function scopeActivated($query): void
    {
        $query->where('activated', '=', true);
    }
}
