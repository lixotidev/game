<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMove extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'player_id',
        'move_number',
        'from_row',
        'from_col',
        'to_row',
        'to_col',
        'captured_positions',
        'is_king_promotion',
        'board_state_after',
    ];

    protected $casts = [
        'captured_positions' => 'array',
        'board_state_after' => 'array',
        'is_king_promotion' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function isCapture(): bool
    {
        return !empty($this->captured_positions);
    }
}
