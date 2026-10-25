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
        Schema::create('utility_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 64)->unique();
            $table->string('service_type', 30); // airtime, data, electricity, betting
            $table->string('provider', 30)->default('bigisub');
            $table->string('provider_reference', 100)->nullable()->index();
            $table->string('provider_status', 50)->nullable();
            
            // Product info
            $table->string('network_or_disco', 50); // mtn, airtel, glo, 9mobile, ikedc, ekedc, sportybet, bet9ja, etc.
            $table->string('product_code', 100)->nullable(); // e.g. plan ID or disco code
            $table->string('product_name', 255)->nullable(); // e.g. "MTN 1GB SME Data (30 Days)"
            $table->string('customer_target', 100); // phone number, meter number, or betting user ID
            $table->string('customer_name', 255)->nullable(); // verified name for electricity/betting
            $table->string('meter_type', 20)->nullable(); // prepaid or postpaid
            
            // Financials
            $table->decimal('amount_ngn', 18, 4);
            $table->decimal('amount_usd', 18, 8);
            $table->decimal('fee_ngn', 18, 4)->default(0.0000);
            $table->decimal('discount_ngn', 18, 4)->default(0.0000);
            $table->decimal('total_charged_usd', 18, 8);
            $table->decimal('exchange_rate', 18, 4)->default(1500.0000);
            
            // Status: pending, completed, failed, refunded, uncertain
            $table->string('status', 30)->default('pending')->index();
            
            // Electricity / Receipt extras
            $table->string('token', 100)->nullable(); // prepaid electricity token e.g. 1234-5678-9012-3456
            $table->string('units', 50)->nullable(); // e.g. "34.5 kWh"
            
            // Audit & Ledgers
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->unsignedBigInteger('wallet_transaction_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();

            $table->index(['user_id', 'service_type']);
            $table->index(['service_type', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_transactions');
    }
};
