<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Increase decimal precision on all wallet-related columns from (x, 2) to (x, 8).
     *
     * Root cause: 100 NGN / 1500 rate = 0.06666... which rounded to 2dp = 0.07
     * Then 0.07 * 1500 displayed as ₦105 (5% inflation).
     * Using 8dp: 0.06666667 * 1500 = ₦100.00 (correct).
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Fix users.walletBalance from decimal(10,2) → decimal(20,8)
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('walletBalance', 20, 8)->default(0.00000000)->change();
        });

        // Fix wallet_transactions columns from decimal(12,2) → decimal(20,8)
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->decimal('amount_usd', 20, 8)->change();
            $table->decimal('balance_before', 20, 8)->change();
            $table->decimal('balance_after', 20, 8)->change();
        });

        // Fix funding_transactions.amount_usd too
        Schema::table('funding_transactions', function (Blueprint $table) {
            $table->decimal('amount_usd', 20, 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('walletBalance', 10, 2)->default(0.00)->change();
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->decimal('amount_usd', 12, 2)->change();
            $table->decimal('balance_before', 12, 2)->change();
            $table->decimal('balance_after', 12, 2)->change();
        });

        Schema::table('funding_transactions', function (Blueprint $table) {
            $table->decimal('amount_usd', 10, 2)->nullable()->change();
        });
    }
};
