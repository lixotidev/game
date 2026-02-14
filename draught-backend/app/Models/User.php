<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'wallet_balance',
        'total_games_played',
        'total_wins',
        'total_losses',
        'total_ties',
        'total_earnings',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function createdGames(): HasMany
    {
        return $this->hasMany(Game::class, 'creator_id');
    }

    public function joinedGames(): HasMany
    {
        return $this->hasMany(Game::class, 'opponent_id');
    }

    public function wonGames(): HasMany
    {
        return $this->hasMany(Game::class, 'winner_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function gameMoves(): HasMany
    {
        return $this->hasMany(GameMove::class, 'player_id');
    }

    // Wallet Methods
    public function creditWallet(float $amount, string $description = null): void
    {
        $this->increment('wallet_balance', $amount);
        $this->refresh();
    }

    public function debitWallet(float $amount, string $description = null): bool
    {
        if ($this->wallet_balance < $amount) {
            return false;
        }

        $this->decrement('wallet_balance', $amount);
        $this->refresh();
        return true;
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->wallet_balance >= $amount;
    }

    // Game Statistics Methods
    public function incrementGamesPlayed(): void
    {
        $this->increment('total_games_played');
    }

    public function incrementWins(): void
    {
        $this->increment('total_wins');
    }

    public function incrementLosses(): void
    {
        $this->increment('total_losses');
    }

    public function incrementTies(): void
    {
        $this->increment('total_ties');
    }

    public function addEarnings(float $amount): void
    {
        $this->increment('total_earnings', $amount);
    }

    public function getWinRate(): float
    {
        if ($this->total_games_played === 0) {
            return 0;
        }

        return round(($this->total_wins / $this->total_games_played) * 100, 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTopPlayers($query, int $limit = 10)
    {
        return $query->orderByDesc('total_wins')
                    ->orderByDesc('total_games_played')
                    ->limit($limit);
    }

    public function scopeTopEarners($query, int $limit = 10)
    {
        return $query->orderByDesc('total_earnings')
                    ->limit($limit);
    }
}
