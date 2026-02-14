<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained('users')->onDelete('cascade');
            $table->integer('move_number');
            $table->integer('from_row');
            $table->integer('from_col');
            $table->integer('to_row');
            $table->integer('to_col');
            $table->json('captured_positions')->nullable();
            $table->boolean('is_king_promotion')->default(false);
            $table->json('board_state_after');
            $table->timestamps();

            $table->index(['game_id', 'move_number']);
            $table->index('player_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_moves');
    }
};
