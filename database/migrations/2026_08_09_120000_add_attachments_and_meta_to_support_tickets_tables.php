<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets', 'category')) {
                $table->string('category', 50)->default('general')->after('subject');
            }
            if (!Schema::hasColumn('support_tickets', 'priority')) {
                $table->string('priority', 20)->default('normal')->after('category');
            }
        });

        Schema::table('support_ticket_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('support_ticket_messages', 'attachment_path')) {
                $table->string('attachment_path', 500)->nullable()->after('message');
            }
            if (!Schema::hasColumn('support_ticket_messages', 'attachment_name')) {
                $table->string('attachment_name', 255)->nullable()->after('attachment_path');
            }
            if (!Schema::hasColumn('support_ticket_messages', 'attachment_type')) {
                $table->string('attachment_type', 50)->nullable()->after('attachment_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['category', 'priority']);
        });

        Schema::table('support_ticket_messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name', 'attachment_type']);
        });
    }
};
