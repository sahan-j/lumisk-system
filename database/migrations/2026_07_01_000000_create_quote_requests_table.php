<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('service_type', ['website', 'mobile_app', 'design', 'maintenance', 'hosting', 'other'])->default('other');
            $table->enum('budget_range', ['under_50k', '50k_150k', '150k_500k', 'over_500k', 'flexible'])->default('flexible');
            $table->enum('timeline', ['asap', '1_month', '3_months', '6_months', 'flexible'])->default('flexible');
            $table->enum('status', ['pending', 'reviewing', 'converted', 'declined'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('converted_estimate_id')->nullable()->constrained('estimates')->nullOnDelete();
            $table->text('declined_reason')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
