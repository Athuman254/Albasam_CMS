<?php

namespace App\Http\Controllers\Payroll;

use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Allowance;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Models\PayrollAllowance;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class EmployeePayrollController extends Controller
{

   public function allowanceAdjustmentDataTable(){
      $statuses = QueryBuilder::for(
            PayrollAllowance::with(['employee','allowance'])->orderBy('month')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
            AllowedFilter::scope('search','Search')
        ])->jsonPaginate();

        return Resource::collection($statuses);
   }
    public function payrollAdjustment(){
      $employees  = Employee::all();
      $allowances = Allowance::where('is_active', true)->get();
      return Inertia::render('Payroll/PayrollAdjustment',[
         'employees'=> $employees,
         'allowances'=> $allowances
      ]);
    }

    public function storeAllowanceAdjustment(Request $request){
      $request->validate([
         'employee_ids'=> 'required|array',
         'allowance_id' => 'required',
         'reason'=> 'nullable',
         'amount' => 'required',
         'date' => 'required|date'
      ]);
         $year = date('Y', strtotime($request->date));

        $month = date('F', strtotime($request->date));
      foreach($request->employee_ids as $employee_id){
         PayrollAllowance::create([
            "employee_id" => $employee_id,
            "allowance_id" => $request->allowance_id,
            "amount" => $request->amount * 100,
            "reason" => $request->reason,
            "is_included" => false,
            "month" => $month,
            "year" => $year,
            "date" =>  $request->date
         ]);
      }
      return back(303)->with('success', 'Payroll Adjustment Allowance  has been created.');
    }

    public function updateAllowanceAdjustment(Request $request, PayrollAllowance $payrollAllowance){
       $request->validate([
         'employee_id'=> 'required',
         'allowance_id' => 'required',
         'reason'=> 'nullable',
         'amount' => 'required',
         'date' => 'required|date'
      ]);
         $year = date('Y', strtotime($request->date));

        $month = date('F', strtotime($request->date));

        $payrollAllowance->update([
          "employee_id" => $request->employee_id,
            "allowance_id" => $request->allowance_id,
            "amount" => $request->amount * 100,
            "reason" => $request->reason,
            "is_included" => false,
            "month" => $month,
            "year" => $year,
            "date" =>  $request->date
        ]);
        return back(303)->with('success', 'Payroll Adjustment Allowance  has been created.');
    }

}
