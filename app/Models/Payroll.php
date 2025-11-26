<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{

   const SALARY = 1;
   const ALLOWANCE = 2;
   const DEDUCTION = 3;
   const PENSION = 4;
   const RELIEF = 0;
   protected $fillable = [
      'month',
      'user_id',
      'employee_id',
      'basic_salary',
      'total_allowances',
      'gross_salary',
      'tax_relief',
      'paye',
      'total_deductions',
      'net_salary',
      'pay_date',
      'job_title',
      'year'
   ];

   public function processedBy()
   {
      return $this->belongsTo(User::class);
   }

   public function scopeShowDetails($query, $date)
   {
      return $query->where('pay_date', '=', $date);
   }
}
