<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix'=> 'V1','middleware'=>['auth:sanctum']], function () {
   Route::get('employees-payroll',[\App\Http\Controllers\Payroll\EmployeePayrollController::class,'index']);
});
