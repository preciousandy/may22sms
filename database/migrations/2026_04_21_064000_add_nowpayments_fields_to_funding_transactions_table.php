<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funding_transactions', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('payment_method');
            $table->string('provider_invoice_id')->nullable()->after('provider');
            $table->string('provider_payment_id')->nullable()->after('provider_invoice_id');
            $table->string('payment_url')->nullable()->after('provider_payment_id');
            $table->string('pay_currency', 20)->nullable()->after('payment_url');
        });
    }

    public function down(): void
    {
        Schema::table('funding_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'provider_invoice_id',
                'provider_payment_id',
                'payment_url',
                'pay_currency',
            ]);
        });
    }
};

