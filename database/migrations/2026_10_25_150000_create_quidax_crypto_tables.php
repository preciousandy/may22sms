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
        Schema::create('crypto_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Unique merchant reference and Quidax references
            $table->string('merchant_reference', 80)->unique();
            $table->string('provider_reference', 80)->nullable()->index();
            $table->string('provider_public_id', 120)->nullable()->index();
            
            // Currency & Conversion Details
            $table->string('from_currency', 20)->default('usdt');
            $table->string('to_currency', 20)->default('ngn');
            $table->string('network', 30)->default('bep20');
            $table->decimal('from_amount', 18, 8);
            $table->decimal('to_amount', 18, 2);
            $table->decimal('exchange_rate', 18, 4);
            $table->decimal('stamp_charge', 12, 2)->default(0.00);
            $table->decimal('processor_fee', 12, 2)->default(0.00);
            $table->decimal('platform_fee', 12, 2)->default(0.00);
            
            // Settlement / Payout Destination
            $table->string('payout_destination', 30)->default('bank_account'); // bank_account, wallet
            $table->string('bank_code', 30)->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('account_number', 40)->nullable();
            $table->string('account_name', 150)->nullable();
            
            // Crypto Deposit Instructions
            $table->string('deposit_address', 180)->nullable();
            $table->string('deposit_tag', 100)->nullable();
            $table->timestamp('deposit_assigned_at')->nullable();
            $table->decimal('crypto_received_amount', 18, 8)->nullable();
            $table->string('crypto_txid', 180)->nullable();
            
            // Fiat Payout Details
            $table->string('fiat_payout_id', 120)->nullable();
            $table->decimal('fiat_payout_amount', 18, 2)->nullable();
            
            // Status & Lifecycle
            // pending, address_assigned, processing, completed, needs_attention, failed
            $table->string('status', 40)->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->boolean('is_test_mode')->default(true)->index();
            $table->boolean('wallet_credited')->default(false);
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
            $table->timestamp('settled_at')->nullable();
            
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('crypto_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('event', 80)->index();
            $table->string('event_id', 120)->nullable()->index();
            $table->string('public_id', 120)->nullable()->index();
            $table->string('merchant_reference', 80)->nullable()->index();
            $table->string('signature', 150)->nullable();
            $table->json('payload');
            $table->boolean('processed')->default(false)->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('crypto_banks_cache', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('public_id', 120)->nullable();
            $table->string('country', 10)->default('NG');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_banks_cache');
        Schema::dropIfExists('crypto_webhooks');
        Schema::dropIfExists('crypto_transactions');
    }
};
