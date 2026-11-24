<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seasonal_advent_days')) {
            Schema::create('seasonal_advent_days', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('day_number')->unique(); // 1..25
                $table->string('title', 140);
                $table->string('description', 255)->nullable();
                $table->string('icon', 40)->default('🎁');
                $table->string('reward_type', 40)->default('wallet_credit'); // wallet_credit, sms_credit, boosting_credit, discount_coupon, promo_code
                $table->decimal('reward_value_usd', 14, 4)->default(0.0500);
                $table->string('promo_code', 60)->nullable();
                $table->decimal('discount_percent', 8, 2)->nullable();
                $table->date('unlock_date')->nullable()->index();
                $table->boolean('force_unlocked')->default(false);
                $table->boolean('is_enabled')->default(true)->index();
                $table->unsignedInteger('claimed_count')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seasonal_advent_claims')) {
            Schema::create('seasonal_advent_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('advent_day_id')->constrained('seasonal_advent_days')->cascadeOnDelete();
                $table->unsignedTinyInteger('day_number');
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('reward_type', 40);
                $table->decimal('reward_value_usd', 14, 4)->default(0.0000);
                $table->string('reward_code', 80)->nullable();
                $table->string('reference', 100)->unique();
                $table->timestamp('claimed_at')->useCurrent();
                $table->timestamps();

                $table->unique(['day_number', 'user_id'], 'seasonal_advent_day_user_unique');
            });
        }

        if (! Schema::hasTable('seasonal_badges')) {
            Schema::create('seasonal_badges', function (Blueprint $table) {
                $table->id();
                $table->string('code', 60)->unique();
                $table->string('name', 120);
                $table->string('description', 255);
                $table->string('icon', 120)->default('⭐');
                $table->string('condition_type', 60)->default('visit_hub'); // visit_hub, save_tree, save_snowman, claim_advent_1, claim_advent_5, complete_challenge, generate_card, explore_village
                $table->unsignedInteger('condition_target')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seasonal_user_badges')) {
            Schema::create('seasonal_user_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seasonal_badge_id')->constrained('seasonal_badges')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('earned_at')->useCurrent();
                $table->timestamps();

                $table->unique(['seasonal_badge_id', 'user_id'], 'seasonal_badge_user_unique');
            });
        }

        if (! Schema::hasTable('seasonal_challenges')) {
            Schema::create('seasonal_challenges', function (Blueprint $table) {
                $table->id();
                $table->string('code', 60)->unique();
                $table->string('title', 160);
                $table->string('description', 255);
                $table->string('icon', 40)->default('🎄');
                $table->string('period_type', 20)->default('daily'); // daily, weekly, seasonal
                $table->string('action_type', 60)->default('visit_christmas_hub'); // visit_christmas_hub, complete_profile, claim_advent_day, decorate_tree, build_snowman, explore_village, generate_greeting_card
                $table->unsignedInteger('target_count')->default(1);
                $table->decimal('reward_value_usd', 14, 4)->default(0.0500);
                $table->dateTime('expires_at')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seasonal_user_challenges')) {
            Schema::create('seasonal_user_challenges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seasonal_challenge_id')->constrained('seasonal_challenges')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('progress_count')->default(0);
                $table->boolean('is_completed')->default(false);
                $table->boolean('is_reward_claimed')->default(false);
                $table->string('claim_reference', 100)->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('claimed_at')->nullable();
                $table->timestamps();

                $table->unique(['seasonal_challenge_id', 'user_id'], 'seasonal_challenge_user_unique');
            });
        }

        if (! Schema::hasTable('seasonal_user_creations')) {
            Schema::create('seasonal_user_creations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->json('tree_config')->nullable();
                $table->json('snowman_config')->nullable();
                $table->json('greeting_card_config')->nullable();
                $table->json('village_Visited_spots')->nullable();
                $table->unsignedInteger('cards_generated_count')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_user_creations');
        Schema::dropIfExists('seasonal_user_challenges');
        Schema::dropIfExists('seasonal_challenges');
        Schema::dropIfExists('seasonal_user_badges');
        Schema::dropIfExists('seasonal_badges');
        Schema::dropIfExists('seasonal_advent_claims');
        Schema::dropIfExists('seasonal_advent_days');
    }
};

