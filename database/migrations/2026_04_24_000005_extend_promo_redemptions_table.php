<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_redemptions', function (Blueprint $table) {
            if (! Schema::hasColumn('promo_redemptions', 'context')) {
                $table->string('context', 30)->default('funding')->after('funding_transaction_id');
            }
            if (! Schema::hasColumn('promo_redemptions', 'subject_type')) {
                $table->string('subject_type', 180)->nullable()->after('context');
            }
            if (! Schema::hasColumn('promo_redemptions', 'subject_id')) {
                $table->string('subject_id', 60)->nullable()->after('subject_type');
            }
            if (! Schema::hasColumn('promo_redemptions', 'amount_usd')) {
                $table->decimal('amount_usd', 12, 2)->nullable()->after('subject_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('promo_redemptions', function (Blueprint $table) {
            if (Schema::hasColumn('promo_redemptions', 'amount_usd')) {
                $table->dropColumn('amount_usd');
            }
            if (Schema::hasColumn('promo_redemptions', 'subject_id')) {
                $table->dropColumn('subject_id');
            }
            if (Schema::hasColumn('promo_redemptions', 'subject_type')) {
                $table->dropColumn('subject_type');
            }
            if (Schema::hasColumn('promo_redemptions', 'context')) {
                $table->dropColumn('context');
            }
        });
    }
};

