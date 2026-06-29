<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->enum('uploaded_by', ['client', 'admin'])->default('client');
            $table->enum('category', ['payment_proof', 'requirements', 'content', 'contract', 'design_feedback', 'other'])->default('other');
            $table->string('title');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->text('description')->nullable();
            $table->boolean('is_visible_to_client')->default(true);
            $table->text('admin_note')->nullable();
            $table->text('client_note')->nullable();
            $table->boolean('viewed_by_admin')->default(false);
            $table->boolean('viewed_by_client')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'uploaded_by']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_documents');
    }
};
