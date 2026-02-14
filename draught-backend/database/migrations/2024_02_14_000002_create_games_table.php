<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('game_code', 6)->unique();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('opponent_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'cancelled'])->default('waiting');
            $table->decimal('bet_amount', 10, 2);
            $table->decimal('total_pot', 10, 2)->default(0.00);
            $table->decimal('admin_commission', 10, 2)->default(0.00);
            $table->decimal('prize_amount', 10, 2)->default(0.00);
            $table->enum('creator_color', ['red', 'black']);
            $table->enum('opponent_color', ['red', 'black'])->nullable();
            $table->enum('current_turn', ['red', 'black'])->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('result', ['win', 'tie', 'cancelled'])->nullable();
            $table->json('board_state')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('game_code');
            $table->index('status');
            $table->index(['creator_id', 'status']);
            $table->index(['opponent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
