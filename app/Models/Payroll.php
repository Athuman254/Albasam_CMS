<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{

   const DEDUCTION = 0;
   const SALARY = 0;
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
}
