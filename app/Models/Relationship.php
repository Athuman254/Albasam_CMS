<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Relationship extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'relationships';
    protected $primaryKey = 'id';
    protected $casts = ['activated' => 'bool'];
    protected $appends = ['hashid'];
    protected $fillable = ['name', 'activated'];

    public function scopeActivated($query)
    {
        $query->where('activated', '=', true);
    }
}
