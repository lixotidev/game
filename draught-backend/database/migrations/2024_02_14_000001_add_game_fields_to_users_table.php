<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->nullable()->after('email');
            $table->string('avatar')->nullable()->after('password');
            $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('avatar');
            $table->integer('total_games_played')->default(0)->after('wallet_balance');
            $table->integer('total_wins')->default(0)->after('total_games_played');
            $table->integer('total_losses')->default(0)->after('total_wins');
            $table->integer('total_ties')->default(0)->after('total_losses');
            $table->decimal('total_earnings', 10, 2)->default(0.00)->after('total_ties');
            $table->boolean('is_active')->default(true)->after('total_earnings');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar',
                'wallet_balance',
                'total_games_played',
                'total_wins',
                'total_losses',
                'total_ties',
                'total_earnings',
                'is_active',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
