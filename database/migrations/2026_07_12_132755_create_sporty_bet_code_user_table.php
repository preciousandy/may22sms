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
        Schema::create('sporty_bet_code_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sporty_bet_code_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate purchases
            $table->unique(['user_id', 'sporty_bet_code_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sporty_bet_code_user');
    }
};
