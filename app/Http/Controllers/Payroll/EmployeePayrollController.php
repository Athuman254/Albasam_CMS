<?php

namespace App\Http\Controllers\Payroll;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmployeePayrollController extends Controller
{
    public function payrollAdjustment(){
      return Inertia::render('Payroll/PayrollAdjustment');
    }
}
