<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    
    // Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/balance', [WalletController::class, 'balance']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
        Route::post('/deposit/initialize', [WalletController::class, 'initializeDeposit']);
        Route::post('/deposit/verify', [WalletController::class, 'verifyDeposit']);
        Route::post('/withdraw/request', [WalletController::class, 'requestWithdrawal']);
        Route::get('/withdraw/banks', [WalletController::class, 'getBanks']);
    });
    
    // Games
    Route::prefix('games')->group(function () {
        Route::get('/lobby', [GameController::class, 'lobby']);
        Route::post('/create', [GameController::class, 'create']);
        Route::post('/{code}/join', [GameController::class, 'join']);
        Route::get('/{id}', [GameController::class, 'show']);
        Route::post('/{id}/move', [GameController::class, 'makeMove']);
        Route::post('/{id}/resign', [GameController::class, 'resign']);
        Route::get('/my-games', [GameController::class, 'myGames']);
    });
    
    // Leaderboard
    Route::get('/leaderboard/top-players', [LeaderboardController::class, 'topPlayers']);
    Route::get('/leaderboard/top-earners', [LeaderboardController::class, 'topEarners']);
});
