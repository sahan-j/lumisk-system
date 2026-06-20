<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->enum('source', [
                'website', 'referral', 'social_media', 'cold_outreach',
                'walk_in', 'whatsapp', 'other',
            ])->default('website');
            $table->foreignId('stage_id')->constrained('pipeline_stages')->cascadeOnDelete();
            $table->decimal('value', 12, 2)->nullable();
            $table->string('currency', 10)->default('LKR');
            $table->integer('probability')->default(50);
            $table->date('expected_close_date')->nullable();
            $table->string('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('converted_to_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['stage_id', 'sort_order']);
            $table->index('converted_to_client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
