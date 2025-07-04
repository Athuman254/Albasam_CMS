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

    public function division(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function stream(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }
    
    public function subjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'rank_subjects', 'rank_id', 'subject_id');
    }
    
    public function scopeActivated($query): void
    {
        $query->where('activated', '=', true);
    }
    
    public function scopeSearch($query, string $terms = null)
    {
        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%'.$term.'%';
            
            $query->where('name', 'like', $term)
                ->orWhereHas('division', function($q) use ($term) {
                    $q->where('name', 'like', $term);
                })->orWhereHas('stream', function($q) use ($term) {
                    $q->where('name', 'like', $term);
                });
        });
    }
}
