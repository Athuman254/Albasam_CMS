<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rank extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'ranks';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $casts = ['activated' => 'bool'];
    protected $fillable = ['name', 'division_id', 'stream_id', 'teacher_id', 'activated'];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function scopeActivated($query)
    {
        $query->where('activated', '=', true);
    }
}
