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
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls');
            $table->bigInteger('employee_id')->nullable();
            $table->integer('amount');
            $table->integer('balance')->nullable();
            $table->boolean('ahl_exempted')->default(false);
            $table->string('month');
            $table->integer('employer')->default(0);
            $table->smallInteger('source');
            $table->string('description');
            $table->boolean('is_insurance')->default(false);
            $table->integer('accumulated_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
