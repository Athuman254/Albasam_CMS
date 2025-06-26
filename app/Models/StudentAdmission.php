<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAdmission extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'student_admissions';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $casts = ['has_exit_school' => 'boolean'];
//    protected $guarded = ['id'];
    protected $fillable = [
        'date', 'date_of_exit', 'division_id', 'has_exit_school'
    ];

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
    
    public function scopeSearch($query, string $terms = null)
    {
        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%'.$term.'%';
            
            $query->whereHas('student', function($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term);
            });
        });
    }
}
