<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes, HasHashid, HashidRouting;

    protected $table = 'employees';
//    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $fillable = [
        'user_id', 'employment_type_id', 'employment_status_id', 'job_title_id', 'honorific_id', 'marital_status_id', 'gender_id', 'religion_id',
        'staff_number', 'date_of_hire', 'first_name', 'middle_name', 'last_name', 'email', 'primary_phone', 'secondary_phone', 'permanent_physical_address',
        'secondary_physical_address', 'postal_address', 'identification_number', 'tax_identification_pin',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function employment_type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EmploymentType::class, 'employment_type_id', 'id');
    }

    public function employment_status(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EmploymentStatus::class, 'employment_status_id', 'id');
    }

    public function job_title(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id', 'id');
    }

    public function honorific(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Honorific::class, 'honorific_id', 'id');
    }

    public function marital_status(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class, 'marital_status_id', 'id');
    }

    public function gender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    public function religion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Religion::class, 'religion_id', 'id');
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function contacts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function qualifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Qualification::class);
    }

    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkHistory::class);
    }
    
    public function scopeSearch($query, string $terms = null)
    {
        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%'.$term.'%';
            
            $query->where('first_name', 'like', $term)
                ->orwhere('last_name', 'like', $term)
                ->orwhere('staff_number', 'like', $term);
        });
    }
}
