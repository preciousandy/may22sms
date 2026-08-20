<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_support')) {
                $table->boolean('is_support')->default(false)->after('is_admin');
            }
            if (! Schema::hasColumn('users', 'is_editor')) {
                $table->boolean('is_editor')->default(false)->after('is_support');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_editor')) {
                $table->dropColumn('is_editor');
            }
            if (Schema::hasColumn('users', 'is_support')) {
                $table->dropColumn('is_support');
            }
        });
    }
};

