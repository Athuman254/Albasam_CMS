<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Drop the old enum column and recreate it with the new values
            DB::statement("ALTER TABLE campaigns MODIFY COLUMN status ENUM('draft', 'scheduled', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'draft'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Revert back to original enum values
            DB::statement("ALTER TABLE campaigns MODIFY COLUMN status ENUM('draft', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'draft'");
        });
    }
};
