<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boost_services', function (Blueprint $table) {
            if (! Schema::hasColumn('boost_services', 'fail_count')) {
                $table->unsignedInteger('fail_count')->default(0)->after('is_active');
                $table->text('last_error')->nullable()->after('fail_count');
                $table->timestamp('last_failed_at')->nullable()->after('last_error');
            }
        });
    }

    public function down(): void
    {
        Schema::table('boost_services', function (Blueprint $table) {
            $table->dropColumn(['fail_count', 'last_error', 'last_failed_at']);
        });
    }
};
