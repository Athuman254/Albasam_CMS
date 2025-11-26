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
        Schema::create('contact_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();

            $table->unique(['phone_number']);
            $table->index(['email']);
        });

        Schema::create('group_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('contact_groups')->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['group_id', 'contact_id']);
        });

        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('content');
            $table->json('variables')->nullable();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', [
                'draft',
                'scheduled',
                'in_progress',
                'completed',
                'failed',
                'cancelled'
            ])->default('draft');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->onDelete('set null');
            $table->text('content');
            $table->enum('status', [
                'pending',
                'queued',
                'sending',
                'sent',
                'delivered',
                'failed',
                'cancelled'
            ])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index(['status', 'scheduled_at']);
            $table->index(['contact_id', 'created_at']);
        });

        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->enum('event_type', [
                'queued',
                'sending',
                'sent',
                'delivered',
                'failed',
                'opened',
                'clicked',
                'bounced',
                'complained',
                'opted_out'
            ]);
            $table->json('event_data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['message_id', 'event_type']);
            $table->index('created_at');
        });

        Schema::create('opt_outs', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('opted_out_at')->useCurrent();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique('phone_number');
            $table->index('opted_out_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opt_outs');
        Schema::dropIfExists('message_logs');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('group_contacts');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('contact_groups');
//        Schema::dropIfExists('users');
    }
};
