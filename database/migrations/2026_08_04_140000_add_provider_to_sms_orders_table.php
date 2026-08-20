<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('sms_orders', 'provider')) {
                $table->string('provider')->default('5sim')->after('promo_code_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sms_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sms_orders', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }
};
