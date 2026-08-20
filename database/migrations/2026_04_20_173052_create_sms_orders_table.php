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
        Schema::create('sms_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_id');
            $table->string('phone_number');
            $table->string('country');
            $table->string('operator');
            $table->string('product');
            $table->decimal('price', 8, 2);
            $table->string('status')->default('PENDING'); // PENDING, RECEIVED, CANCELED, FINISHED
            $table->string('sms_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_orders');
    }
};
