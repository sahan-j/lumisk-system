<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'cheque', 'other'])->default('cash');
            $table->string('receipt')->nullable();
            $table->string('reference_number')->nullable();
            $table->boolean('is_billable')->default(false);
            $table->boolean('is_billed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('expense_date');
            $table->index('category_id');
            $table->index('client_id');
            $table->index(['is_billable', 'is_billed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
