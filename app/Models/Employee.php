<?php

namespace App\Models;

use App\Traits\HasHashid;
use App\Traits\HashidRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable, SoftDeletes, HasHashid, HashidRouting;

    protected string $guard = 'employee';

    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $appends = ['hashid'];
    protected $casts = ['use_existing_user' => 'bool', 'has_system_access' => 'bool'];
    protected $fillable = [
        'use_existing_user', 'user_id', 'employment_type_id', 'employment_status_id', 'honorific_id', 'marital_status_id', 'gender_id', 'religion_id',
        'staff_number', 'date_of_hire', 'first_name', 'middle_name', 'last_name', 'email', 'primary_phone', 'secondary_phone', 'permanent_physical_address',
        'secondary_physical_address', 'postal_address', 'identification_number', 'tax_identification_pin', 'has_system_access', 'password','in_payroll','pays_paye','pays_sha','sha_no','pays_nssf','nssf_no','pays_housing_levy'
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

    public static function generateStaffNumber(): string
    {
        $lastEmployee = Employee::orderBy('id', 'desc')->first();
        $prefix = 'EMP-';
        $month = now()->format('m');
        $year = now()->format('y');

        // Determine the next number
        if ($lastEmployee) {
            $lastCode = $lastEmployee->staff_number;
            $lastNumber = intval(substr($lastCode, 4, 3)); // Extract the number part from EMP-xxxmy
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return "{$prefix}{$nextNumber}{$month}{$year}";
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

    public function incomes(){
      return $this->hasMany(EmployeeIncome::class);
    }

    public function deductions(){
       return $this->hasMany(EmployeeDeduction::class);
    }

    public function basicSalary(){
      return $this->hasOne(EmployeeIncome::class)->whereHas('income', function($q){
         return $q->where('name', 'like','%basic%');
      });
    }
   //  public function allowances(){
   //    return $this->hasMany(Allowance::class);
   //  }
}
