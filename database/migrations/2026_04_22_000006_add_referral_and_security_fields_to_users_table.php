<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->unique();
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_code', 20)->nullable();
            $table->timestamp('two_factor_expires_at')->nullable();

            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 60)->nullable();
            $table->text('last_login_user_agent')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropUnique(['referral_code']);
            $table->dropColumn([
                'referral_code',
                'two_factor_enabled',
                'two_factor_code',
                'two_factor_expires_at',
                'last_login_at',
                'last_login_ip',
                'last_login_user_agent',
            ]);
        });
    }
};
