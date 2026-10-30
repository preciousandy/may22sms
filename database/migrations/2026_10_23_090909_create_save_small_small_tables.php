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
        // 1. Savings Categories
        Schema::create('savings_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 50)->default('🎯');
            $table->text('description')->nullable();
            $table->decimal('default_annual_rate', 5, 2)->default(16.90);
            $table->decimal('min_deposit_ngn', 18, 4)->default(500.00);
            $table->decimal('min_deposit_usd', 18, 4)->default(0.50);
            $table->unsignedInteger('min_duration_days')->default(30);
            $table->unsignedInteger('max_duration_days')->default(365);
            $table->boolean('allow_early_withdrawal')->default(true);
            $table->boolean('allow_partial_withdrawal')->default(false);
            $table->decimal('early_withdrawal_fee_percent', 5, 2)->default(2.50);
            $table->decimal('early_withdrawal_interest_forfeited_percent', 5, 2)->default(100.00);
            $table->decimal('maturity_fee_percent', 5, 2)->default(1.50); // 1.5% of earned interest
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();
        });

        // 2. Savings Plans
        Schema::create('savings_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('savings_categories')->nullOnDelete();
            $table->string('reference', 64)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('currency', 10)->default('NGN');
            $table->decimal('target_amount', 20, 8)->nullable();
            $table->decimal('principal_amount', 20, 8)->default(0.00000000);
            $table->decimal('accrued_interest', 20, 8)->default(0.00000000);
            $table->decimal('paid_interest', 20, 8)->default(0.00000000);
            $table->decimal('forfeited_interest', 20, 8)->default(0.00000000);
            $table->decimal('total_withdrawn', 20, 8)->default(0.00000000);
            $table->decimal('annual_interest_rate', 5, 2)->default(16.90);
            $table->date('interest_commencement_date');
            $table->date('last_accrual_date')->nullable();
            $table->date('start_date');
            $table->date('maturity_date');
            $table->unsignedInteger('duration_days');
            $table->boolean('allow_early_withdrawal')->default(true);
            $table->boolean('allow_partial_withdrawal')->default(false);
            $table->decimal('early_break_fee_percent', 5, 2)->default(2.50);
            $table->decimal('early_break_interest_forfeit_percent', 5, 2)->default(100.00);
            $table->decimal('maturity_fee_percent', 5, 2)->default(1.50);
            $table->string('compounding_frequency', 20)->default('daily');
            $table->string('status', 30)->default('active')->index();
            $table->timestamp('matured_at')->nullable();
            $table->timestamp('broken_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('payout_statement')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'maturity_date']);
        });

        // 3. Savings Contributions & Ledger Entries
        Schema::create('savings_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_plan_id')->constrained('savings_plans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 64)->unique();
            $table->unsignedBigInteger('wallet_transaction_id')->nullable();
            $table->string('type', 40); // deposit, additional_deposit, early_withdrawal, maturity_payout, interest_credit
            $table->decimal('amount', 20, 8);
            $table->decimal('principal_before', 20, 8)->default(0.00000000);
            $table->decimal('principal_after', 20, 8)->default(0.00000000);
            $table->string('description');
            $table->string('status', 30)->default('completed');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['savings_plan_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });

        // 4. Daily Interest Accruals (Idempotent tracking)
        Schema::create('savings_interest_accruals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_plan_id')->constrained('savings_plans')->cascadeOnDelete();
            $table->date('accrual_date');
            $table->decimal('eligible_principal', 20, 8);
            $table->decimal('annual_rate', 5, 2);
            $table->decimal('day_interest_amount', 20, 8);
            $table->decimal('cumulative_interest_before', 20, 8);
            $table->decimal('cumulative_interest_after', 20, 8);
            $table->boolean('is_leap_year')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['savings_plan_id', 'accrual_date'], 'unique_plan_accrual_date');
            $table->index(['accrual_date']);
        });

        // 5. Audit Logs
        Schema::create('savings_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('savings_plan_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('event', 60);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_audit_logs');
        Schema::dropIfExists('savings_interest_accruals');
        Schema::dropIfExists('savings_contributions');
        Schema::dropIfExists('savings_plans');
        Schema::dropIfExists('savings_categories');
    }
};
