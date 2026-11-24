<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seasonal_modes')) {
            Schema::create('seasonal_modes', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('slug', 80)->unique();
                $table->string('theme_type', 40)->default('custom'); // christmas, new_year, valentines, easter, halloween, independence, black_friday, anniversary, ramadan, custom
                $table->string('icon', 40)->nullable(); // emoji or icon class
                $table->string('greeting_title', 255)->nullable();
                $table->text('greeting_subtitle')->nullable();
                $table->string('badge_text', 100)->nullable();
                $table->string('primary_color', 20)->default('#0d9488');
                $table->string('secondary_color', 20)->default('#0f766e');
                $table->string('accent_color', 20)->default('#facc15');
                $table->string('banner_gradient', 255)->nullable();
                $table->string('banner_image', 255)->nullable();
                $table->string('background_image', 255)->nullable();
                $table->string('animation_type', 40)->default('snow'); // snow, fireworks_confetti, hearts, easter_eggs, pumpkins, green_white_confetti, gold_sparkles, lanterns_stars, none
                $table->string('animation_intensity', 20)->default('medium'); // low, medium, high
                $table->boolean('animations_enabled')->default(true);
                $table->boolean('decorations_enabled')->default(true);
                $table->boolean('banner_enabled')->default(true);
                $table->boolean('countdown_enabled')->default(false);
                $table->dateTime('countdown_target_at')->nullable();
                $table->json('display_pages')->nullable(); // ['all'] or ['home', 'dashboard', 'sms', 'boosting', 'logs', 'funding']
                $table->json('visual_effects')->nullable(); // extra flags e.g. header_garland, glowing_borders, floating_badges
                $table->boolean('is_active')->default(false)->index();
                $table->boolean('is_scheduled')->default(false)->index();
                $table->boolean('manual_override')->default(false);
                $table->dateTime('starts_at')->nullable()->index();
                $table->dateTime('ends_at')->nullable()->index();
                $table->boolean('is_system_default')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seasonal_settings')) {
            Schema::create('seasonal_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seasonal_gifts')) {
            Schema::create('seasonal_gifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seasonal_mode_id')->nullable()->constrained('seasonal_modes')->nullOnDelete();
                $table->string('name', 160);
                $table->text('description')->nullable();
                $table->string('icon_or_image', 255)->nullable();
                $table->string('gift_type', 40)->default('wallet_credit'); // wallet_credit, sms_credit, boosting_credit, discount_coupon, promo_code, service_credit, custom_reward
                $table->decimal('reward_value_usd', 14, 4)->default(0.0000);
                $table->string('promo_code', 60)->nullable();
                $table->decimal('discount_percent', 8, 2)->nullable();
                $table->string('distribution_mode', 40)->default('manual_claim'); // manual_claim, auto_distribute, selected_users, all_eligible
                $table->string('claim_button_text', 100)->default('Claim Your Seasonal Gift 🎁');
                $table->unsignedInteger('max_total_claims')->default(0); // 0 = unlimited
                $table->unsignedInteger('max_claims_per_user')->default(1);
                $table->unsignedInteger('claimed_count')->default(0);
                $table->decimal('total_distributed_usd', 16, 4)->default(0.0000);
                $table->unsignedInteger('min_account_age_days')->default(0);
                $table->decimal('min_wallet_funding_usd', 14, 4)->default(0.0000);
                $table->unsignedInteger('min_transactions_count')->default(0);
                $table->json('eligible_user_ids')->nullable();
                $table->dateTime('starts_at')->nullable()->index();
                $table->dateTime('expires_at')->nullable()->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seasonal_gift_claims')) {
            Schema::create('seasonal_gift_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seasonal_gift_id')->constrained('seasonal_gifts')->cascadeOnDelete();
                $table->foreignId('seasonal_mode_id')->nullable()->constrained('seasonal_modes')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('gift_type', 40);
                $table->decimal('reward_value_usd', 14, 4)->default(0.0000);
                $table->string('reward_code', 100)->nullable();
                $table->string('distribution_method', 40)->default('manual_claim'); // manual_claim, auto_distribute, admin_selected, admin_bulk
                $table->string('reference', 100)->unique();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('claimed_at')->useCurrent();
                $table->timestamps();

                $table->unique(['seasonal_gift_id', 'user_id'], 'seasonal_gift_user_unique');
                $table->index(['user_id', 'claimed_at']);
            });
        }

        if (! Schema::hasTable('seasonal_announcements')) {
            Schema::create('seasonal_announcements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seasonal_mode_id')->nullable()->constrained('seasonal_modes')->nullOnDelete();
                $table->string('title', 200);
                $table->text('message');
                $table->string('badge_text', 60)->nullable();
                $table->string('cta_text', 80)->nullable();
                $table->string('cta_link', 255)->nullable();
                $table->boolean('is_dismissible')->default(true);
                $table->boolean('show_on_dashboard')->default(true);
                $table->boolean('show_on_home')->default(true);
                $table->boolean('send_in_app_notification')->default(false);
                $table->boolean('send_email_notification')->default(false);
                $table->dateTime('starts_at')->nullable()->index();
                $table->dateTime('expires_at')->nullable()->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seasonal_user_preferences')) {
            Schema::create('seasonal_user_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->boolean('animations_disabled')->default(false);
                $table->json('dismissed_announcements')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seasonal_audit_logs')) {
            Schema::create('seasonal_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('actor_id')->nullable()->index();
                $table->string('actor_name', 150)->nullable();
                $table->string('action', 100)->index();
                $table->string('target_type', 80)->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->text('summary')->nullable();
                $table->json('payload')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_audit_logs');
        Schema::dropIfExists('seasonal_user_preferences');
        Schema::dropIfExists('seasonal_announcements');
        Schema::dropIfExists('seasonal_gift_claims');
        Schema::dropIfExists('seasonal_gifts');
        Schema::dropIfExists('seasonal_settings');
        Schema::dropIfExists('seasonal_modes');
    }
};

