<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_number')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 8)->default('LKR');
            $table->enum('billing_cycle', ['weekly', 'monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired', 'paused'])->default('active');
            $table->date('start_date');
            $table->date('trial_end_date')->nullable();
            $table->date('next_billing_date');
            $table->date('last_billed_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->boolean('auto_invoice')->default(true);
            $table->boolean('auto_send_invoice')->default(true);
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('client_id');
            $table->index('next_billing_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
