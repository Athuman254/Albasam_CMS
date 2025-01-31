<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Religion extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'religions';
    protected $primaryKey = 'id';
    protected $casts = ['activated' => 'bool'];
    protected $appends = ['hashid'];
    protected $fillable = ['name', 'activated'];

    public function scopeActivated($query)
    {
        $query->where('activated', '=', true);
    }
}
