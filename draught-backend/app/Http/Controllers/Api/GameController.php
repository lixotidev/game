<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function __construct(protected GameService $gameService)
    {
    }

    /**
     * List available games in lobby.
     */
    public function lobby()
    {
        $games = Game::where('status', 'waiting')
            ->with('creator:id,name,avatar')
            ->latest()
            ->get();

        return response()->json($games);
    }

    /**
     * Create a new game.
     */
    public function create(Request $request)
    {
        $request->validate([
            'bet_amount' => 'required|numeric|min:50',
        ]);

        try {
            $game = $this->gameService->createGame($request->user(), $request->bet_amount);
            
            // In a real app, broadcast GameCreated event here

            return response()->json($game, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Join an existing game.
     */
    public function join(Request $request, $code)
    {
        try {
            $game = $this->gameService->joinGame($request->user(), $code);
            
            // In a real app, broadcast GameJoined event here

            return response()->json($game);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get game details.
     */
    public function show($id)
    {
        $game = Game::with(['creator', 'opponent', 'winner', 'moves.player'])
            ->findOrFail($id);

        return response()->json($game);
    }

    /**
     * Make a move.
     */
    public function makeMove(Request $request, $id)
    {
        $request->validate([
            'from' => 'required|array',
            'from.row' => 'required|integer|min:0|max:7',
            'from.col' => 'required|integer|min:0|max:7',
            'to' => 'required|array',
            'to.row' => 'required|integer|min:0|max:7',
            'to.col' => 'required|integer|min:0|max:7',
            'captured' => 'nullable|array',
            'is_king_promotion' => 'nullable|boolean',
        ]);

        $game = Game::findOrFail($id);

        try {
            $updatedGame = $this->gameService->makeMove($game, $request->user(), $request->all());
            
            // In a real app, broadcast MoveMade event here

            return response()->json($updatedGame);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Resign from game.
     */
    public function resign(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        try {
            $updatedGame = $this->gameService->resignGame($game, $request->user());
            
            // In a real app, broadcast GameEnded event here

            return response()->json($updatedGame);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get user's game history.
     */
    public function myGames(Request $request)
    {
        $user = $request->user();
        $games = Game::where(function($query) use ($user) {
                $query->where('creator_id', $user->id)
                      ->orWhere('opponent_id', $user->id);
            })
            ->with(['creator', 'opponent', 'winner'])
            ->latest()
            ->paginate(15);

        return response()->json($games);
    }
}
