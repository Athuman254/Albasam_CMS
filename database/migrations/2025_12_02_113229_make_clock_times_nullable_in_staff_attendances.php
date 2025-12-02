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
        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->dateTime('clock_in_time')->nullable()->change();
            $table->dateTime('clock_out_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->dateTime('clock_in_time')->nullable(false)->change();
            $table->dateTime('clock_out_time')->nullable(false)->change();
        });
    }
};
