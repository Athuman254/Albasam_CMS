<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'student_admission_id', 'admission_number', 'rank_id', 'first_name', 'middle_name', 'last_name', 'date_of_birth', 'birth_certificate_number', 'gender_id', 'religion_id',
        'citizenship', 'county', 'ward', 'permanent_address', 'previous_school', 'kcpe_score', 'physical_disability', 'hobby', 'medical_details', 'character_book',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(StudentAdmission::class, 'student_admission_id', 'id');
    }
    
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'rank_id', 'id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class, 'religion_id', 'id');
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(Sibling::class);
    }

    public function scopeClassfilter($query, $terms = ''){
        collect($terms)->filter()->each(function ($term) use ($query) {

            $term = '%'.$term.'%';

            return $query->whereHas('rank', function ($q) use ($term) {
                $q->where('name', 'like', $term);
            });
        });
    }
}
