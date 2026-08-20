<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boost_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jap_service_id')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->decimal('rate', 12, 4);
            $table->unsignedInteger('min')->default(0);
            $table->unsignedInteger('max')->default(0);
            $table->boolean('is_active')->default(true);
            $table->decimal('price_override', 12, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boost_services');
    }
};

