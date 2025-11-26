<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhooks\MpesaAutoRecordController;


Route::post('/webhook/mpesa-payment', [MpesaAutoRecordController::class, 'handlePayment']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix'=> 'V1','middleware'=>['auth:sanctum']], function () {
   Route::get('employees-payroll',[\App\Http\Controllers\Payroll\EmployeePayrollController::class,'index']);
});