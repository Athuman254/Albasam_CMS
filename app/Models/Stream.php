<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stream extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'streams';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $casts = ['activated' => 'bool'];
    protected $fillable = ['name', 'activated'];

    public function scopeActivated($query)
    {
        $query->where('activated', '=', true);
    }
}
