<?php

namespace App\Services;

use App\Events\GameCreated;
use App\Events\GameEnded;
use App\Events\GameJoined;
use App\Events\MoveMade;
use App\Models\Game;
use App\Models\GameMove;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GameService
{
    private const ADMIN_COMMISSION_PERCENTAGE = 25;

    public function createGame(User $user, float $betAmount): Game
    {
        // Validate user has sufficient balance
        if (!$user->hasSufficientBalance($betAmount)) {
            throw new \Exception('Insufficient balance');
        }

        return DB::transaction(function () use ($user, $betAmount) {
            // Debit user wallet
            $user->debitWallet($betAmount);

            // Create game
            $game = Game::create([
                'game_code' => Game::generateGameCode(),
                'creator_id' => $user->id,
                'bet_amount' => $betAmount,
                'creator_color' => 'red', // Creator is always red
                'status' => 'waiting',
            ]);

            // Create transaction
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'bet_placed',
                'amount' => $betAmount,
                'status' => 'completed',
                'game_id' => $game->id,
                'description' => "Bet placed for game {$game->game_code}",
                'completed_at' => now(),
            ]);

            // Broadcast GameCreated event
            event(new GameCreated($game));

            return $game->fresh();
        });
    }

    public function joinGame(User $user, string $gameCode): Game
    {
        $game = Game::where('game_code', $gameCode)->firstOrFail();

        // Validate game can be joined
        if (!$game->canJoin($user)) {
            throw new \Exception('Cannot join this game');
        }

        return DB::transaction(function () use ($user, $game) {
            // Debit user wallet
            $user->debitWallet($game->bet_amount);

            // Calculate pot and commission
            $totalPot = $game->bet_amount * 2;
            $adminCommission = $totalPot * (self::ADMIN_COMMISSION_PERCENTAGE / 100);
            $prizeAmount = $totalPot - $adminCommission;

            // Update game
            $game->update([
                'opponent_id' => $user->id,
                'opponent_color' => 'black', // Opponent is always black
                'status' => 'in_progress',
                'total_pot' => $totalPot,
                'admin_commission' => $adminCommission,
                'prize_amount' => $prizeAmount,
                'current_turn' => 'red', // Red (creator) starts
                'board_state' => $this->initializeBoard(),
                'started_at' => now(),
            ]);

            // Create transaction for opponent
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'bet_placed',
                'amount' => $game->bet_amount,
                'status' => 'completed',
                'game_id' => $game->id,
                'description' => "Bet placed for game {$game->game_code}",
                'completed_at' => now(),
            ]);

            // Broadcast GameJoined event
            event(new GameJoined($game));

            return $game->fresh();
        });
    }

    public function makeMove(Game $game, User $user, array $moveData): Game
    {
        // Validate it's user's turn
        if (!$game->isPlayerTurn($user)) {
            throw new \Exception('Not your turn');
        }

        // Validate move
        if (!$this->isValidMove($game->board_state, $moveData, $game->getPlayerColor($user))) {
            throw new \Exception('Invalid move');
        }

        return DB::transaction(function () use ($game, $user, $moveData) {
            // Apply move to board
            $newBoardState = $this->applyMove($game->board_state, $moveData);

            // Save move
            $moveNumber = $game->moves()->count() + 1;
            GameMove::create([
                'game_id' => $game->id,
                'player_id' => $user->id,
                'move_number' => $moveNumber,
                'from_row' => $moveData['from']['row'] ?? $moveData['fromRow'],
                'from_col' => $moveData['from']['col'] ?? $moveData['fromCol'],
                'to_row' => $moveData['to']['row'] ?? $moveData['toRow'],
                'to_col' => $moveData['to']['col'] ?? $moveData['toCol'],
                'captured_positions' => $moveData['captured'] ?? [],
                'is_king_promotion' => $moveData['is_king_promotion'] ?? false,
                'board_state_after' => $newBoardState,
            ]);

            // Update game board
            $game->update(['board_state' => $newBoardState]);

            // Check for win/tie
            $gameResult = $this->checkGameEnd($newBoardState, $game);

            if ($gameResult) {
                $this->endGame($game, $gameResult);
            } else {
                // Switch turn
                $game->switchTurn();
                
                // Broadcast MoveMade event
                event(new MoveMade($game, $moveData));
            }

            return $game->fresh();
        });
    }

    private function initializeBoard(): array
    {
        $board = array_fill(0, 8, array_fill(0, 8, null));

        // Place black pieces (top 3 rows)
        for ($row = 0; $row < 3; $row++) {
            for ($col = 0; $col < 8; $col++) {
                if (($row + $col) % 2 == 1) {
                    $board[$row][$col] = [
                        'color' => 'black',
                        'type' => 'normal',
                    ];
                }
            }
        }

        // Place red pieces (bottom 3 rows)
        for ($row = 5; $row < 8; $row++) {
            for ($col = 0; $col < 8; $col++) {
                if (($row + $col) % 2 == 1) {
                    $board[$row][$col] = [
                        'color' => 'red',
                        'type' => 'normal',
                    ];
                }
            }
        }

        return $board;
    }

    private function isValidMove(array $board, array $move, string $playerColor): bool
    {
        $fromRow = $move['from']['row'] ?? $move['fromRow'];
        $fromCol = $move['from']['col'] ?? $move['fromCol'];
        $toRow = $move['to']['row'] ?? $move['toRow'];
        $toCol = $move['to']['col'] ?? $move['toCol'];

        if (!isset($board[$fromRow][$fromCol]) || $board[$fromRow][$fromCol]['color'] !== $playerColor) {
            return false;
        }

        if (isset($board[$toRow][$toCol])) {
            return false;
        }

        if (abs($toRow - $fromRow) !== abs($toCol - $fromCol)) {
            return false;
        }

        return true;
    }

    private function applyMove(array $board, array $move): array
    {
        $fromRow = $move['from']['row'] ?? $move['fromRow'];
        $fromCol = $move['from']['col'] ?? $move['fromCol'];
        $toRow = $move['to']['row'] ?? $move['toRow'];
        $toCol = $move['to']['col'] ?? $move['toCol'];

        $piece = $board[$fromRow][$fromCol];
        $board[$toRow][$toCol] = $piece;
        $board[$fromRow][$fromCol] = null;

        if (!empty($move['captured'])) {
            foreach ($move['captured'] as $captured) {
                $board[$captured['row']][$captured['col']] = null;
            }
        }

        if (($piece['color'] === 'red' && $toRow === 0) || 
            ($piece['color'] === 'black' && $toRow === 7)) {
            $board[$toRow][$toCol]['type'] = 'king';
        }

        return $board;
    }

    private function checkGameEnd(array $board, Game $game): ?array
    {
        $redPieces = 0;
        $blackPieces = 0;

        foreach ($board as $row) {
            foreach ($row as $cell) {
                if ($cell) {
                    if ($cell['color'] === 'red') $redPieces++;
                    if ($cell['color'] === 'black') $blackPieces++;
                }
            }
        }

        if ($redPieces === 0) {
            return ['result' => 'win', 'winner_color' => 'black'];
        }

        if ($blackPieces === 0) {
            return ['result' => 'win', 'winner_color' => 'red'];
        }

        $recentMoves = $game->moves()->latest()->take(50)->get();
        if ($recentMoves->count() === 50 && $recentMoves->every(fn($m) => empty($m->captured_positions))) {
            return ['result' => 'tie'];
        }

        return null;
    }

    private function endGame(Game $game, array $result): void
    {
        if ($result['result'] === 'win') {
            $winnerColor = $result['winner_color'];
            $winnerId = $winnerColor === $game->creator_color 
                ? $game->creator_id 
                : $game->opponent_id;
            
            $winner = User::find($winnerId);
            $loserId = $winnerId === $game->creator_id 
                ? $game->opponent_id 
                : $game->creator_id;
            $loser = User::find($loserId);

            $game->update([
                'status' => 'completed',
                'result' => 'win',
                'winner_id' => $winnerId,
                'completed_at' => now(),
            ]);

            $winner->creditWallet($game->prize_amount);
            $winner->incrementWins();
            $winner->incrementGamesPlayed();
            $winner->addEarnings($game->prize_amount - $game->bet_amount);

            $loser->incrementLosses();
            $loser->incrementGamesPlayed();

            Transaction::create([
                'user_id' => $winnerId,
                'type' => 'bet_won',
                'amount' => $game->prize_amount,
                'status' => 'completed',
                'game_id' => $game->id,
                'description' => "Won game {$game->game_code}",
                'completed_at' => now(),
            ]);

            Transaction::create([
                'user_id' => null,
                'type' => 'commission',
                'amount' => $game->admin_commission,
                'status' => 'completed',
                'game_id' => $game->id,
                'description' => "Admin commission from game {$game->game_code}",
                'completed_at' => now(),
            ]);

        } elseif ($result['result'] === 'tie') {
            $splitAmount = $game->prize_amount / 2;

            $game->update([
                'status' => 'completed',
                'result' => 'tie',
                'completed_at' => now(),
            ]);

            $creator = User::find($game->creator_id);
            $opponent = User::find($game->opponent_id);

            $creator->creditWallet($splitAmount);
            $creator->incrementTies();
            $creator->incrementGamesPlayed();

            $opponent->creditWallet($splitAmount);
            $opponent->incrementTies();
            $opponent->incrementGamesPlayed();

            foreach ([$game->creator_id, $game->opponent_id] as $userId) {
                Transaction::create([
                    'user_id' => $userId,
                    'type' => 'bet_won',
                    'amount' => $splitAmount,
                    'status' => 'completed',
                    'game_id' => $game->id,
                    'description' => "Tie in game {$game->game_code}",
                    'completed_at' => now(),
                ]);
            }

            Transaction::create([
                'user_id' => null,
                'type' => 'commission',
                'amount' => $game->admin_commission,
                'status' => 'completed',
                'game_id' => $game->id,
                'description' => "Admin commission from game {$game->game_code}",
                'completed_at' => now(),
            ]);
        }

        // Broadcast GameEnded event
        event(new GameEnded($game));
    }

    public function resignGame(Game $game, User $user): Game
    {
        if (!$game->isInProgress()) {
            throw new \Exception('Game is not in progress');
        }

        $opponentId = $game->creator_id === $user->id 
            ? $game->opponent_id 
            : $game->creator_id;

        $opponent = User::find($opponentId);
        $this->endGame($game, [
            'result' => 'win',
            'winner_color' => $game->getPlayerColor($opponent),
        ]);

        return $game->fresh();
    }
}
