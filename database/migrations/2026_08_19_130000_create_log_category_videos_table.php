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
        Schema::create('log_category_videos', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->index(); // e.g. facebook, tiktok, instagram, twitter, youtube, discord, other
            $table->string('title');
            $table->text('video_url')->nullable(); // external link or embed link
            $table->string('video_file')->nullable(); // path to uploaded video file
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_category_videos');
    }
};
