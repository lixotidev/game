<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Get top players by wins.
     */
    public function topPlayers()
    {
        $players = User::topPlayers(20)
            ->select('id', 'name', 'avatar', 'total_wins', 'total_games_played')
            ->get();

        return response()->json($players);
    }

    /**
     * Get top earners.
     */
    public function topEarners()
    {
        $earners = User::topEarners(20)
            ->select('id', 'name', 'avatar', 'total_earnings')
            ->get();

        return response()->json($earners);
    }
}
