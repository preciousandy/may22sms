<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing zero fee categories to standard 2.50%
        if (Schema::hasTable('savings_categories')) {
            DB::table('savings_categories')
                ->where('early_withdrawal_fee_percent', '<=', 0.00)
                ->update(['early_withdrawal_fee_percent' => 2.50]);
        }

        // Update existing zero fee active plans to standard 2.50%
        if (Schema::hasTable('savings_plans')) {
            DB::table('savings_plans')
                ->where('early_break_fee_percent', '<=', 0.00)
                ->update(['early_break_fee_percent' => 2.50]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
