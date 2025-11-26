<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->foreignId('auto_recorded_payment_id')
                  ->nullable()
                  ->after('verified_at')
                  ->constrained('auto_recorded_payments')
                  ->onDelete('set null');
                  
            $table->index(['auto_recorded_payment_id'], 'fee_payments_auto_recorded_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['auto_recorded_payment_id']);
            $table->dropIndex('fee_payments_auto_recorded_id_index');
            $table->dropColumn('auto_recorded_payment_id');
        });
    }
};