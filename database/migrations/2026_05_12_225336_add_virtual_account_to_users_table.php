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
        Schema::table('users', function (Blueprint $table) {
            $table->string('paymentpoint_account_number')->nullable();
            $table->string('paymentpoint_account_name')->nullable();
            $table->string('paymentpoint_bank_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'paymentpoint_account_number',
                'paymentpoint_account_name',
                'paymentpoint_bank_name',
            ]);
        });
    }
};
