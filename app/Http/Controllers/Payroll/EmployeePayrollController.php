<?php

namespace App\Http\Controllers\Payroll;

use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Allowance;
use App\Models\Deduction;
use Illuminate\Http\Request;
use App\Models\EmployeeIncome;
use App\Http\Resources\Resource;
use App\Models\PayrollAllowance;
use App\Models\PayrollDeduction;
use App\Http\Controllers\Controller;
use App\Models\EmployeeDeduction;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class EmployeePayrollController extends Controller
{
   public function employeeIncomeDataTable()
   {
      $employeeincomes =  QueryBuilder::for(
         EmployeeIncome::with(['employee', 'income'])->orderBy('id', 'desc')
      )->allowedFilters([
         AllowedFilter::scope('search'),
         AllowedFilter::exact('employee_id'),
         AllowedFilter::partial('rank_id'),
      ])->jsonPaginate();

      return Resource::collection($employeeincomes);
   }

      public function employeeDeductionDataTable()
   {
      $employeeDeductions =  QueryBuilder::for(
         EmployeeDeduction::with(['employee', 'deduction'])->orderBy('id', 'desc')
      )->allowedFilters([
         AllowedFilter::scope('search'),
         AllowedFilter::exact('employee_id'),
         AllowedFilter::partial('rank_id'),
      ])->jsonPaginate();

      return Resource::collection($employeeDeductions);
   }
   public function getEmployees(Request $request)
   {
      $employees = Employee::with(['employment_type','basicSalary'])->where('in_payroll', true)->get();
      return Resource::collection($employees);
   }

   public function allowanceAdjustmentDataTable()
   {
      $statuses = QueryBuilder::for(
         PayrollAllowance::with(['employee', 'allowance'])->orderBy('month')
      )->allowedFilters([
         AllowedFilter::exact('id'),
         AllowedFilter::exact('activated'),
         AllowedFilter::partial('name'),
         AllowedFilter::scope('search', 'Search')
      ])->jsonPaginate();

      return Resource::collection($statuses);
   }

   public function deductionAdjustmentDataTable()
   {
      $statuses = QueryBuilder::for(
         PayrollDeduction::with(['employee', 'deduction'])->orderBy('month')
      )->allowedFilters([
         AllowedFilter::exact('id'),
         AllowedFilter::exact('activated'),
         AllowedFilter::partial('name'),
         AllowedFilter::scope('search', 'Search')
      ])->jsonPaginate();

      return Resource::collection($statuses);
   }
   public function payrollAdjustment()
   {
      $employees  = Employee::all();
      $allowances = Allowance::where('is_active', true)->get();
      $deductions = Deduction::where('is_active', true)->get();
      return Inertia::render('Payroll/PayrollAdjustment', [
         'employees' => $employees,
         'allowances' => $allowances,
         'deductions' => $deductions
      ]);
   }

   public function storeAllowanceAdjustment(Request $request)
   {
      $request->validate([
         'employee_ids' => 'required|array',
         'allowance_id' => 'required',
         'reason' => 'nullable',
         'amount' => 'required',
         'date' => 'required|date'
      ]);
      $year = date('Y', strtotime($request->date));

      $month = date('F', strtotime($request->date));
      foreach ($request->employee_ids as $employee_id) {
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

   public function storeDeductionAdjustment(Request $request)
   {
      $request->validate([
         'employee_ids' => 'required|array',
         'deduction_id' => 'required',
         'reason' => 'nullable',
         'amount' => 'required',
         'date' => 'required|date'
      ]);

      $year = date('Y', strtotime($request->date));

      $month = date('F', strtotime($request->date));
      foreach ($request->employee_ids as $employee_id) {
         PayrollDeduction::create([
            "employee_id" => $employee_id,
            "deduction_id" => $request->deduction_id,
            "amount" => $request->amount * 100,
            "reason" => $request->reason,
            "is_included" => false,
            "month" => $month,
            "year" => $year,
            "date" =>  $request->date
         ]);
      }
      return back(303)->with('success', 'Payroll Adjustment Deduction  has been created.');
   }
   public function updateAllowanceAdjustment(Request $request, PayrollAllowance $payrollAllowance)
   {
      $request->validate([
         'employee_id' => 'required',
         'allowance_id' => 'required',
         'reason' => 'nullable',
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
   public function updateDeductionAdjustment(Request $request, PayrollDeduction $payrollDeduction)
   {
      $request->validate([
         'employee_id' => 'required',
         'deduction_id' => 'required',
         'reason' => 'nullable',
         'amount' => 'required',
         'date' => 'required|date'
      ]);
      $year = date('Y', strtotime($request->date));

      $month = date('F', strtotime($request->date));

      $payrollDeduction->update([
         "employee_id" => $request->employee_id,
         "deduction_id" => $request->deduction_id,
         "amount" => $request->amount * 100,
         "reason" => $request->reason,
         "is_included" => false,
         "month" => $month,
         "year" => $year,
         "date" =>  $request->date
      ]);
      return back(303)->with('success', 'Payroll Adjustment Deduction  has been created.');
   }

   public function storeEmployeeIncome(Request $request)
   {
      $request->validate([
         'employee_id' => 'required',
         'income_id' => 'required',
         'amount' => 'required',
      ]);
      EmployeeIncome::create([
         'employee_id' => $request->employee_id,
         'income_id' => $request->income_id,
         'amount' => $request->amount * 100,
      ]);
      return back(303)->with('success', 'Employee Income has been created.');
   }

   public function updateEmployeeIncome(Request $request, EmployeeIncome $employeeIncome)
   {
      $request->validate([
         'employee_id' => 'required',
         'income_id' => 'required',
         'amount' => 'required',
      ]);
      $employeeIncome->update([
         'employee_id' => $request->employee_id,
         'income_id' => $request->income_id,
         'amount' => $request->amount * 100,
      ]);
      return back(303)->with('success', 'Employee Income has been update.');
   }

   public function deleteEmployeeIncome(EmployeeIncome $employeeIncome)
   {
      $employeeIncome->delete();
      return back(303)->with('success', 'Employee Income has been deleted.');
   }
   public function storeEmployeeDeduction(Request $request)
   {

      $request->validate([
         'employee_id' => 'required',
         'deduction_id' => 'required',
         'amount' => 'required',
      ]);
      EmployeeDeduction::create([
         'employee_id' => $request->employee_id,
         'deduction_id' => $request->deduction_id,
         'amount' => $request->amount * 100,
      ]);
      return back(303)->with('success', 'Employee Deduction has been created.');
   }
public function updateEmployeeDeduction(Request $request, EmployeeDeduction $employeeDeduction)
   {
      $request->validate([
         'employee_id' => 'required',
         'deduction_id' => 'required',
         'amount' => 'required',
      ]);
      $employeeDeduction->update(
[
         'employee_id' => $request->employee_id,
         'deduction_id' => $request->deduction_id,
         'amount' => $request->amount * 100,
      ]
);
      return back(303)->with('success', 'Employee Deduction has been updated.');
   }

      public function deleteEmployeeDedection(EmployeeDeduction $employeeDeduction)
   {
      $employeeDeduction->delete();
      return back(303)->with('success', 'Employee deduction has been deleted.');
   }

}
