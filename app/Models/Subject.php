<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;
    
    protected $table = 'subjects';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $casts = ['activated' => 'bool'];
    protected $fillable = ['name', 'code', 'group', 'activated'];
    
    const LANGUAGES = 1;
    const SCIENCE = 2;
    const APPLIED_SCIENCE = 3;
    const HUMANITIES = 4;
    const CREATIVE_ARTS = 5;
    const TECHNICAL_SUBJECTS = 6;
    
//    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
//    {
//        return $this->belongsToMany(Student::class, 'student_subject');
//    }
    
    public function classes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Rank::class, 'rank_subject');
    }
}
