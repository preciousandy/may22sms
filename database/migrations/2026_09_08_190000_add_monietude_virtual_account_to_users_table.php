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
            $table->string('monietude_account_number')->nullable()->index();
            $table->string('monietude_account_name')->nullable();
            $table->string('monietude_bank_name')->nullable();
            $table->string('monietude_provider')->nullable()->default('palmpay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'monietude_account_number',
                'monietude_account_name',
                'monietude_bank_name',
                'monietude_provider',
            ]);
        });
    }
};
