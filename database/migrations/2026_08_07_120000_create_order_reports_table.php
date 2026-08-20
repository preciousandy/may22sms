<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_reports')) {
            Schema::create('order_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('order_type'); // sms, boost, logs
                $table->unsignedBigInteger('order_id');
                $table->text('reason')->nullable();
                $table->string('status')->default('pending'); // pending, resolved, dismissed
                $table->text('admin_notes')->nullable();
                $table->timestamps();

                $table->index(['order_type', 'order_id']);
                $table->index('status');
            });
        }

        Schema::table('sms_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('sms_orders', 'is_reported')) {
                $table->boolean('is_reported')->default(false)->after('status');
                $table->timestamp('reported_at')->nullable()->after('is_reported');
                $table->string('report_status')->nullable()->after('reported_at');
            }
        });

        Schema::table('boost_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('boost_orders', 'is_reported')) {
                $table->boolean('is_reported')->default(false)->after('status');
                $table->timestamp('reported_at')->nullable()->after('is_reported');
                $table->string('report_status')->nullable()->after('reported_at');
            }
        });

        Schema::table('log_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('log_orders', 'is_reported')) {
                $table->boolean('is_reported')->default(false)->after('status');
                $table->timestamp('reported_at')->nullable()->after('is_reported');
                $table->string('report_status')->nullable()->after('reported_at');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_reports');

        Schema::table('sms_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sms_orders', 'is_reported')) {
                $table->dropColumn(['is_reported', 'reported_at', 'report_status']);
            }
        });

        Schema::table('boost_orders', function (Blueprint $table) {
            if (Schema::hasColumn('boost_orders', 'is_reported')) {
                $table->dropColumn(['is_reported', 'reported_at', 'report_status']);
            }
        });

        Schema::table('log_orders', function (Blueprint $table) {
            if (Schema::hasColumn('log_orders', 'is_reported')) {
                $table->dropColumn(['is_reported', 'reported_at', 'report_status']);
            }
        });
    }
};
