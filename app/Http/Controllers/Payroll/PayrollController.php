<?php

namespace App\Http\Controllers\Payroll;

use App\Models\Tax;
use Inertia\Inertia;
use App\Models\Income;
use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Throwable;

class PayrollController extends Controller
{
   public function run()
   {
      return Inertia::render("Payroll/PayrollRun");
   }

   private function calculateNSSF($grossPay)
   {
      $rate = 0.06;
      $tier1Limit = 800000;
      $tier2MaxLimit = 7200000;

      $tier1Contribution = min($grossPay, $tier1Limit) * $rate;

      $tier2Contribution = 0;
      if ($grossPay > $tier1Limit) {
         $tier2Contribution = min($grossPay - $tier1Limit, $tier2MaxLimit - $tier1Limit) * $rate;
      }

      $employeeContribution = $tier1Contribution + $tier2Contribution;

      $employerContribution = $employeeContribution;

      return [
         'employeeContribution' => $employeeContribution,
         'employerContribution' => $employerContribution,
         'totalContribution'    => ($employeeContribution + $employerContribution),
      ];
   }

   private function calculateHouseLevy($gross_pay)
   {
      $AHLRelief = 0;
      $limit = 900000;
      $rate = 1.5;

      if ($rate) {
         // $rate = HousingLevy::where('year', date('Y'))->first();
         $amount = $gross_pay * ($rate * 0.01);
         $AHLReliefRaw = $amount * 0.15;
         if ($AHLReliefRaw <= $limit) {

            $AHLRelief = $AHLReliefRaw;
         } else {
            $AHLRelief = $limit;
         }
         $housingLevy = [
            'HousingLevy' => $amount,
            'AHLRelief'   => $AHLRelief,
         ];

         return $housingLevy;
      } else {
         // $rate = HousingLevy::whereNot('activated', 0)->first();
         $amount = $gross_pay * ($rate * 0.01);
         $AHLReliefRaw = $amount * 0.15;
         if ($AHLReliefRaw <= $limit) {

            $AHLRelief = $AHLReliefRaw;
         } else {
            $AHLRelief = $limit;
         }
         $housingLevy = [
            'HousingLevy' => $amount,
            'AHLRelief'   => $AHLRelief,
         ];

         return $housingLevy;
      }
   }

   private function calculateSHIF($grossPay)
   {
      return $grossPay * 0.0275;
   }
   private function calcutatePAYE($employee, $earnings)
   {
      $grossPay = 0;
      $shifContribution = 0;
      $housingLevy = 0;
      $totalPensions = 0;
      $nssf = 0;
      $totalIncome = 0;
      $basic_pay = 0;
      $basic_pay_id = Income::where('name', '=', 'Basic salary')->first();
      $totalBenefits = 0;
      $totalAllowances = 0;
      $totalPayrollAllowances = 0;
      $payAfterTax = 0;
      $AHLexempted = 0;


      //   foreach ($employee->allowances ?? [] as $allowance) {

      //       $totalAllowances += $allowance->amount ?? 0;

      //   }

      //        if has payroll allowances
      //   foreach ($employee->payrollAllowances ?? [] as $allowance) {
      //       $totalPayrollAllowances += $allowance->amount ?? 0;
      //       if ($allowance->ahl_exempted) {
      //           $AHLexempted += $allowance->amount ?? 0;
      //       }
      //   }

      foreach ($earnings as $earning) {
         if ($earning->income_id == $basic_pay_id->id) {
            $basic_pay = $earning->amount ?? 0;
         }
         $totalIncome += $earning->amount ?? 0;
      }

      //        benefits
      $totalEarning = $totalIncome;
      //        allowances
      $totalIncome += $totalAllowances;



      //        payroll allowances
      $totalIncome += $totalPayrollAllowances;

      $grossPay = $totalIncome;

      if($employee->pays_housing_levy){
         $housing_levy = $this->calculateHouseLevy(($grossPay - $AHLexempted));
         $housingLevy = $housing_levy['HousingLevy'];
      }

      if($employee->pays_sha){
         $shifCon = $this->calculateSHIF($totalIncome);
         // in shillling, round off and change back to cents
         $shifCon = (round($shifCon * 0.01) * 100);
         $shifContribution += $shifCon;
      }


      if($employee->pays_nssf){
         $nssf = $this->calculateNSSF($totalIncome);
         $totalPensions += $nssf['employeeContribution'];
      }

      if ($totalPensions <= 2000000) {
         $totalIncome -= $totalPensions;
      } else {
         $totalIncome -= 2000000;
      }
      // echo $totalPensions . '<br/>';
      // echo $shifContribution. '<br/>';
      // echo $housingLevy. '<br/>';

      // remove shif
      $totalIncome -= $shifContribution;
      $totalIncome -= $housingLevy;
      // dd($totalIncome);
      $paye = 0;
      $remainingIncome = $totalIncome;

      // dd($employee->pays_paye);
      $taxRelief = 0;
      if ($totalIncome > 2400000) {
         if ($employee->pays_paye) {
            $taxRelief = 240000;
            $taxTable = Tax::where('year', '=', now()->year)->get();
            if ($taxTable->count() <= 0) {
               $taxTable = Tax::all();
            }
            $paye = 0;
            foreach ($taxTable as $taxBand) {
               if ($remainingIncome > $taxBand->band) {
                  $paye += $taxBand->band * $taxBand->rate ?? 0;
                  $remainingIncome -= $taxBand->band ?? 0;
               } else {
                  $paye += $remainingIncome * $taxBand->rate ?? 0;
                  break;
               }
            }
         }
      }


      $paye = $paye - $taxRelief;
      $payAfterTax = $totalIncome - max($paye, 0);
      $totalStatutory = [
         'PAYEE'       => max($paye, 0),
         'NSSF'        => $nssf['employeeContribution'] ?? 0,
         'HOUSINGLEVY' => $housingLevy,
         'SHA'         => $shifContribution,
      ];

      return [
         'paye'                        => max($paye, 0),
         'employee_nssf'               => $nssf['employeeContribution'] ?? 0,
         'employer_nssf'               => $nssf['employeeContribution'] ?? 0,
         'tax_relief'                  => $taxRelief,
         'totalIncome'                 => $totalEarning,
         'totalAllowance'              => $totalAllowances,
         'totalBenefits'               => $totalBenefits,
         'grossPay'                    => $grossPay,
         'basic_pay'                   => $basic_pay,
         'shifContribution'            => $shifContribution,
         'housing_levy'                => $housingLevy,
         'payAfterTax'                 => $payAfterTax,
         'totalStatutory'              => $totalStatutory,
         'payAfterAllowableDeductions' => ($payAfterTax - ($shifContribution + $housingLevy + $nssf['employeeContribution'])),
      ];
   }


    private function netPay($deductions, $grossPay, $totalStatutory)
    {

        // total allowances
        $housing_levy = 0;
        $totalAllowances = 0;

        // total pensions
      //   $totalPensions = 0;
      //   foreach ($pensions as $pension) {
      //       $totalPensions += $pension->employee_max;
      //   }

        // deductions
        $totalDeductions = 0;
        foreach ($deductions as $deduction) {
            $totalDeductions += $deduction->amount;
        }

      //   foreach ($payrollDeductions as $deduction) {
      //       $totalDeductions += $deduction->amount;
      //   }

        $TOTAL_DEDUCTIONS = $totalDeductions + $totalStatutory['PAYEE'] + $totalStatutory['NSSF'] + $totalStatutory['HOUSINGLEVY'] + $totalStatutory['SHA'];

        $NET_SALARY = $grossPay - $TOTAL_DEDUCTIONS;

        return [
            'total_allowances'    => $totalAllowances,
            'gross_pay'           => $grossPay,
            'housing_levy'        => $housing_levy,
            'total_deductions'    => $TOTAL_DEDUCTIONS,
            'net_pay'             => $NET_SALARY,
        ];

    }
   public function store(Request $request)
   {
      $validated = $request->validate([
         'date'    => 'required|date',
         'employeesIds' => 'array',
      ]);

      // dd($validated);
      $year = date('Y', strtotime($validated['date']));

      $month = date('F', strtotime($validated['date']));
      $payroll = Payroll::where('year', '=', $year)
         ->where('month', '=', $month)
         ->exists();

      if ($payroll) {
         return response()->json(['message' => 'There is an existing payroll for this date'], 400);
      }

      foreach ($validated['employeesIds'] as $emply) {
         $employee = Employee::find($emply);
         if (!$employee) {
            continue;
         }
         $employee->load(['incomes', 'deductions']);

         $payee = $this->calcutatePAYE($employee, $employee->incomes ?? []);
         // dd($payee);
         $netPay = $this->netPay( $employee->deductions ?? [],  $payee['grossPay'], $payee['totalStatutory']);
         dd($netPay);
         DB::beginTransaction();
         try{
             $payroll = Payroll::create([
                    'month'               => $month,
                    'user_id'             => auth()->user()->id,
                    'employee_payroll_id' => $employee->id,
                    'basic_pay'           => $payee['basic_pay'],
                    'allowance'           => $payee['totalAllowance'],
                    'pension'             => ($netPay['pension'] + $payee['employee_nssf']),
                    'gross_pay'           => $payee['grossPay'],
                    'tax_relief'          => $payee['tax_relief'],
                    'paye'                => $payee['paye'],
                    'shif'                => $payee['shifContribution'],
                    'benefits'            => $payee['totalBenefits'],
                    'total_deductions'    => $netPay['total_deductions'],
                    'contributions'       => $netPay['total_contributions'],
                    'net_pay'             => $netPay['net_pay'],
                    'nssf'                => $payee['employee_nssf'],
                    'employer_nssf'       => $payee['employer_nssf'],
                    'pay_date'            => $validated['period'],
                    'job_title'           => $employee->job_title,
                    'year'                => $year,
                ]);
                dd('none');
         }catch(Throwable $ex){

         }
         //    $netPay = $this->netPay($employee, $employee->pensions ?? [], $employee->contributions ?? [], $employee->reliefs ?? [], $employee->deductions ?? [], $employee->payrollDeductions ?? [], $validated['period'], $payee['payAfterAllowableDeductions'], $payee['grossPay'], $payee['totalStatutory']);

         // dd($employee);
      }
   }
}
