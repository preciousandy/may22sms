<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('promo_codes', 'title')) {
                $table->string('title', 120)->nullable()->after('code');
            }
            if (! Schema::hasColumn('promo_codes', 'message')) {
                $table->string('message', 255)->nullable()->after('title');
            }
            if (! Schema::hasColumn('promo_codes', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('promo_codes', 'applies_to')) {
                $table->json('applies_to')->nullable()->after('message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            if (Schema::hasColumn('promo_codes', 'applies_to')) {
                $table->dropColumn('applies_to');
            }
            if (Schema::hasColumn('promo_codes', 'is_public')) {
                $table->dropColumn('is_public');
            }
            if (Schema::hasColumn('promo_codes', 'message')) {
                $table->dropColumn('message');
            }
            if (Schema::hasColumn('promo_codes', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};

