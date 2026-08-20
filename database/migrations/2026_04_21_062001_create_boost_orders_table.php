<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boost_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boost_service_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('jap_order_id')->nullable()->index();
            $table->string('link');
            $table->unsignedInteger('quantity');
            $table->decimal('charge', 12, 4)->default(0);
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('start_count')->nullable();
            $table->unsignedBigInteger('remains')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boost_orders');
    }
};

