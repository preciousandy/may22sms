<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category')->default('general');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->text('maintenance_message')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('last_status_changed_at')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('service_status_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('service_key')->index();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('admin_email')->nullable();
            $table->boolean('previous_status');
            $table->boolean('new_status');
            $table->string('reason')->nullable();
            $table->text('maintenance_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('service_id')
                ->references('id')
                ->on('service_configurations')
                ->nullOnDelete();

            $table->foreign('admin_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // Seed initial core services
        $now = now();
        $defaultServices = [
            [
                'key' => 'airtime',
                'name' => 'Airtime Recharge',
                'category' => 'utilities',
                'icon' => 'phone',
                'description' => 'Automated VTU airtime recharge for MTN, Airtel, Glo, and 9mobile.',
                'is_enabled' => true,
                'maintenance_message' => 'Airtime recharge service is temporarily undergoing scheduled maintenance. Please try again shortly.',
                'last_status_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'data',
                'name' => 'Data Bundles',
                'category' => 'utilities',
                'icon' => 'wifi',
                'description' => 'SME, Corporate Gifting, and Direct data bundles for all mobile networks.',
                'is_enabled' => true,
                'maintenance_message' => 'Data bundle recharge service is temporarily undergoing scheduled maintenance. Please try again shortly.',
                'last_status_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'electricity',
                'name' => 'Electricity Payments',
                'category' => 'utilities',
                'icon' => 'bolt',
                'description' => 'Prepaid meter token generation and postpaid utility bill payments.',
                'is_enabled' => true,
                'maintenance_message' => 'Electricity meter recharge is temporarily unavailable. Please try again shortly.',
                'last_status_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'betting',
                'name' => 'Betting Wallet Top-Up',
                'category' => 'utilities',
                'icon' => 'target',
                'description' => 'Instant wallet funding for SportyBet, Bet9ja, 1xBet, and other punter platforms.',
                'is_enabled' => true,
                'maintenance_message' => 'Betting wallet funding is temporarily unavailable. Please try again shortly.',
                'last_status_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'sms_verify',
                'name' => 'SMS Verification',
                'category' => 'verification',
                'icon' => 'device',
                'description' => 'Real-time virtual phone numbers for instant online SMS verification codes.',
                'is_enabled' => true,
                'maintenance_message' => 'SMS Verification is temporarily unavailable due to upstream provider maintenance. Please try again later.',
                'last_status_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'boosting',
                'name' => 'Social Boosting',
                'category' => 'social',
                'icon' => 'rocket',
                'description' => 'Engagement, followers, likes, and views boosting across major social networks.',
                'is_enabled' => true,
                'maintenance_message' => 'Social Boosting service is temporarily unavailable. Please try again later.',
                'last_status_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'crypto_to_naira',
                'name' => 'Crypto to Naira Conversion',
                'category' => 'fintech',
                'icon' => 'coin',
                'description' => 'Dedicated crypto deposit addresses with automated conversion to Naira bank transfer.',
                'is_enabled' => true,
                'maintenance_message' => 'Crypto to Naira conversion is temporarily paused. Please check back later.',
                'last_status_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'digital_logs',
                'name' => 'Digital Logs Store',
                'category' => 'accounts',
                'icon' => 'document',
                'description' => 'Instant delivery marketplace for digital logs and verified social media accounts.',
                'is_enabled' => true,
                'maintenance_message' => 'Digital Logs Store is temporarily undergoing inventory maintenance.',
                'last_status_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'sportybet',
                'name' => 'SportyBet Codes',
                'category' => 'sports',
                'icon' => 'ball',
                'description' => 'SportyBet booking codes and accumulator slip market.',
                'is_enabled' => true,
                'maintenance_message' => 'SportyBet codes service is temporarily paused.',
                'last_status_changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('service_configurations')->insert($defaultServices);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_status_audit_logs');
        Schema::dropIfExists('service_configurations');
    }
};
