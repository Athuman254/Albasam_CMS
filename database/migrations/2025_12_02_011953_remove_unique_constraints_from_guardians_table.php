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
        Schema::table('guardians', function (Blueprint $table) {
            // Drop unique constraints on email and identification_number
            $table->dropUnique('guardians_email_unique');
            $table->dropUnique('guardians_identification_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            // Restore unique constraints
            $table->unique('email', 'guardians_email_unique');
            $table->unique('identification_number', 'guardians_identification_number_unique');
        });
    }
};
