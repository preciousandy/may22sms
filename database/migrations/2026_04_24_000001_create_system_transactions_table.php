<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('type', 40)->index();
            $table->string('action', 80)->index();
            $table->string('status', 40)->nullable()->index();

            $table->string('subject_type', 191)->nullable()->index();
            $table->string('subject_id', 64)->nullable()->index();

            $table->decimal('amount_usd', 12, 2)->nullable();
            $table->string('reference', 191)->nullable()->index();

            $table->boolean('reversible')->default(false)->index();
            $table->timestamp('reverted_at')->nullable()->index();
            $table->unsignedBigInteger('reverted_by')->nullable()->index();

            $table->timestamp('approved_at')->nullable()->index();
            $table->unsignedBigInteger('approved_by')->nullable()->index();

            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('meta')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_transactions');
    }
};

