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
            $table->string('strollwallet_account_number')->nullable();
            $table->string('strollwallet_account_name')->nullable();
            $table->string('strollwallet_bank_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'strollwallet_account_number',
                'strollwallet_account_name',
                'strollwallet_bank_name',
            ]);
        });
    }
};
