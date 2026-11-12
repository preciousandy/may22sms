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
        Schema::table('utility_transactions', function (Blueprint $table) {
            $table->decimal('provider_cost_ngn', 18, 4)->default(0.0000)->after('amount_usd');
            $table->decimal('provider_cost_usd', 18, 8)->default(0.00000000)->after('provider_cost_ngn');
            $table->decimal('selling_price_ngn', 18, 4)->default(0.0000)->after('provider_cost_usd');
            $table->decimal('profit_ngn', 18, 4)->default(0.0000)->after('total_charged_usd');
            $table->decimal('profit_usd', 18, 8)->default(0.00000000)->after('profit_ngn');
            $table->json('pricing_snapshot')->nullable()->after('profit_usd');

            $table->index(['service_type', 'status', 'created_at']);
        });

        Schema::create('utility_pricing_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('service_type', 40); // data, airtime, electricity, betting, global
            $table->string('setting_key', 80);
            $table->text('previous_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['service_type', 'created_at']);
            $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_pricing_audit_logs');

        Schema::table('utility_transactions', function (Blueprint $table) {
            $table->dropIndex(['service_type', 'status', 'created_at']);
            $table->dropColumn([
                'provider_cost_ngn',
                'provider_cost_usd',
                'selling_price_ngn',
                'profit_ngn',
                'profit_usd',
                'pricing_snapshot',
            ]);
        });
    }
};
