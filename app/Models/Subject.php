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

    public function examSubjects(){
      return $this->hasMany(ExamSubject::class);
    }

    /**
     * Relationship with skills
     */
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * Get active skills
     */
    public function activeSkills()
    {
        return $this->hasMany(Skill::class)->where('is_active', true);
    }

    /**
     * Get exams through skills (if needed)
     */
    public function examsThroughSkills()
    {
        return $this->hasManyThrough(
            Exam::class,
            Skill::class,
            'subject_id', // Foreign key on skills table
            'id', // Foreign key on exams table
            'id', // Local key on subjects table
            'id' // Local key on skills table (though this is indirect)
        );
    }

    /**
     * Scope for active subjects
     */
    public function scopeActive($query)
    {
        return $query->where('activated', true);
    }

    /**
     * Get group name as string
     */
    public function getGroupNameAttribute()
    {
        return match($this->group) {
            self::LANGUAGES => 'Languages',
            self::SCIENCE => 'Science',
            self::APPLIED_SCIENCE => 'Applied Science',
            self::HUMANITIES => 'Humanities',
            self::CREATIVE_ARTS => 'Creative Arts',
            self::TECHNICAL_SUBJECTS => 'Technical Subject',
            default => 'Unknown',
        };
    }
}