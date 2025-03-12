<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;

class RankSubject extends Model
{
    use HasHashid, HashidRouting;
    
    protected $table = 'rank_subjects';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'rank_id', 'subject_id', 'teacher_id'
    ];
    
    public function rank(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Rank::class, 'rank_id', 'id');
    }
    
    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }
    
    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }
}
