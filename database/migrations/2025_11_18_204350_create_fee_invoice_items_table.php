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
        Schema::create('fee_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_invoice_id')->constrained()->onDelete('cascade');
            $table->string('item_name');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('fee_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_invoice_items');
    }
};