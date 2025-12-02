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
            $table->string('username')->unique()->nullable()->after('admission_number');
            $table->string('password')->nullable()->after('username');
            $table->enum('user_type', ['student'])->default('student')->after('password');
            $table->timestamp('password_changed_at')->nullable()->after('user_type');
            $table->boolean('force_password_change')->default(true)->after('password_changed_at');
            $table->rememberToken()->after('force_password_change');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'password',
                'user_type',
                'password_changed_at',
                'force_password_change',
                'remember_token',
            ]);
        });
    }
};
