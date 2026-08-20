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
        Schema::create('sms_countries', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g. 'russia', 'usa'
            $table->string('name'); // e.g. 'Russia', 'USA'
            $table->string('iso')->nullable(); // e.g. 'RU', 'US'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_countries');
    }
};
