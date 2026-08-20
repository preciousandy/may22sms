<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('log_orders', 'promo_code_id')) {
                $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete()->after('user_id');
            }
            if (! Schema::hasColumn('log_orders', 'promo_discount_usd')) {
                $table->decimal('promo_discount_usd', 10, 2)->default(0)->after('total_amount');
            }
        });

        Schema::table('sms_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('sms_orders', 'promo_code_id')) {
                $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete()->after('user_id');
            }
            if (! Schema::hasColumn('sms_orders', 'promo_discount_usd')) {
                $table->decimal('promo_discount_usd', 8, 2)->default(0)->after('price');
            }
        });

        Schema::table('boost_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('boost_orders', 'promo_code_id')) {
                $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete()->after('boost_service_id');
            }
            if (! Schema::hasColumn('boost_orders', 'promo_discount_usd')) {
                $table->decimal('promo_discount_usd', 12, 4)->default(0)->after('charge');
            }
        });
    }

    public function down(): void
    {
        Schema::table('boost_orders', function (Blueprint $table) {
            if (Schema::hasColumn('boost_orders', 'promo_discount_usd')) {
                $table->dropColumn('promo_discount_usd');
            }
            if (Schema::hasColumn('boost_orders', 'promo_code_id')) {
                $table->dropConstrainedForeignId('promo_code_id');
            }
        });

        Schema::table('sms_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sms_orders', 'promo_discount_usd')) {
                $table->dropColumn('promo_discount_usd');
            }
            if (Schema::hasColumn('sms_orders', 'promo_code_id')) {
                $table->dropConstrainedForeignId('promo_code_id');
            }
        });

        Schema::table('log_orders', function (Blueprint $table) {
            if (Schema::hasColumn('log_orders', 'promo_discount_usd')) {
                $table->dropColumn('promo_discount_usd');
            }
            if (Schema::hasColumn('log_orders', 'promo_code_id')) {
                $table->dropConstrainedForeignId('promo_code_id');
            }
        });
    }
};

