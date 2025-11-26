<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   /**
    * Run the migrations.
    */
   public function up(): void
   {
      Schema::create('employee_incomes', function (Blueprint $table) {
         $table->id();
         $table->unsignedBigInteger('employee_id');
         $table->unsignedBigInteger('income_id');
         $table->integer('amount');
         $table->boolean('freeze')->default(false);

         $table->foreign('employee_id')->references('id')->on('employees');
         $table->foreign('income_id')->references('id')->on('incomes');
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('employee_incomes');
   }
};
