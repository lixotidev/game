<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_code',
        'creator_id',
        'opponent_id',
        'status',
        'bet_amount',
        'total_pot',
        'admin_commission',
        'prize_amount',
        'creator_color',
        'opponent_color',
        'current_turn',
        'winner_id',
        'result',
        'board_state',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'board_state' => 'array',
        'bet_amount' => 'decimal:2',
        'total_pot' => 'decimal:2',
        'admin_commission' => 'decimal:2',
        'prize_amount' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function moves(): HasMany
    {
        return $this->hasMany(GameMove::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Helper Methods
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canJoin(User $user): bool
    {
        return $this->isWaiting() 
            && $this->creator_id !== $user->id
            && $user->wallet_balance >= $this->bet_amount;
    }

    public function isPlayerTurn(User $user): bool
    {
        if ($this->creator_id === $user->id) {
            return $this->current_turn === $this->creator_color;
        }
        
        if ($this->opponent_id === $user->id) {
            return $this->current_turn === $this->opponent_color;
        }

        return false;
    }

    public function getPlayerColor(User $user): ?string
    {
        if ($this->creator_id === $user->id) {
            return $this->creator_color;
        }
        
        if ($this->opponent_id === $user->id) {
            return $this->opponent_color;
        }

        return null;
    }

    public function switchTurn(): void
    {
        $this->current_turn = $this->current_turn === 'red' ? 'black' : 'red';
        $this->save();
    }

    // Generate unique game code
    public static function generateGameCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        } while (self::where('game_code', $code)->exists());

        return $code;
    }
}
