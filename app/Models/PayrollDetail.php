<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    protected $fillable = [
      'payroll_id',
      'employee_id',
      'amount',
      'balance',
      'ahl_exempted',
      'is_insurance',
      'accumulated_amount',
      'month',
      'employer',
      'description',
      'source'
    ];
}
