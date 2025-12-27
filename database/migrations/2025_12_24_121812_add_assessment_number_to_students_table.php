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
        Schema::table('students', function (Blueprint $table) {
            $table->string('assessment_number')->nullable()->after('admission_number');
        });

        Schema::table('admission_applications', function (Blueprint $table) {
            $table->string('assessment_number')->nullable()->after('application_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('assessment_number');
        });

        Schema::table('admission_applications', function (Blueprint $table) {
            $table->dropColumn('assessment_number');
        });
    }
};
