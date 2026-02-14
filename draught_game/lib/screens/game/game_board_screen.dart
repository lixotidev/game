import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/game_provider.dart';
import '../../models/draught_models.dart';
import '../../widgets/game/draught_board.dart';

class GameBoardScreen extends StatelessWidget {
  final String? player1Name;
  final String? player2Name;
  final double betAmount;

  const GameBoardScreen({
    super.key,
    this.player1Name,
    this.player2Name,
    this.betAmount = 0,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppColors.backgroundGradient,
        ),
        child: SafeArea(
          child: Consumer<GameProvider>(
            builder: (context, gameProvider, child) {
              return Column(
                children: [
                  // Game Code display for Creator
                  if (gameProvider.isRemote && !gameProvider.gameStarted && gameProvider.playerColor == PieceColor.red)
                    _buildGameCodeHeader(context, gameProvider.remoteGameCode ?? ''),
                  
                  if (gameProvider.isRemote && !gameProvider.gameStarted)
                    _buildWaitingStatus(),

                  // Top Player Info (Black)
                  _buildPlayerInfo(
                    context,
                    name: player2Name ?? 'Player 2',
                    color: PieceColor.black,
                    isActive: gameProvider.currentTurn == PieceColor.black,
                    pieceCount: gameProvider.getPieceCount(PieceColor.black),
                  ),

                  const SizedBox(height: 16),

                  // Game Board
                  Expanded(
                    child: Center(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: const DraughtBoardWidget(),
                      ),
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Bottom Player Info (Red)
                  _buildPlayerInfo(
                    context,
                    name: player1Name ?? 'Player 1',
                    color: PieceColor.red,
                    isActive: gameProvider.currentTurn == PieceColor.red,
                    pieceCount: gameProvider.getPieceCount(PieceColor.red),
                  ),

                  const SizedBox(height: 8),

                  // Game Controls
                  _buildGameControls(context, gameProvider),

                  const SizedBox(height: 16),
                ],
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildPlayerInfo(
    BuildContext context, {
    required String name,
    required PieceColor color,
    required bool isActive,
    required int pieceCount,
  }) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isActive ? AppColors.surface : AppColors.surfaceLight,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isActive ? AppColors.primary : Colors.transparent,
          width: 2,
        ),
        boxShadow: isActive
            ? [
                BoxShadow(
                  color: AppColors.primary.withOpacity(0.3),
                  blurRadius: 10,
                  spreadRadius: 2,
                ),
              ]
            : [],
      ),
      child: Row(
        children: [
          // Player Avatar/Color Indicator
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: color == PieceColor.red
                  ? AppColors.redPiece
                  : AppColors.blackPiece,
              border: Border.all(
                color: color == PieceColor.red
                    ? AppColors.redPieceLight
                    : AppColors.blackPieceLight,
                width: 2,
              ),
            ),
            child: Center(
              child: Icon(
                Icons.person,
                color: Colors.white,
                size: 30,
              ),
            ),
          ),
          const SizedBox(width: 12),

          // Player Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(
                      Icons.circle,
                      size: 12,
                      color: color == PieceColor.red
                          ? AppColors.redPiece
                          : AppColors.blackPiece,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      '$pieceCount pieces',
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Turn Indicator
          if (isActive)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Consumer<GameProvider>(
                builder: (context, gp, _) {
                  final isMyTurn = !gp.isRemote || gp.currentTurn == gp.playerColor;
                  final isLocalPlayerCard = !gp.isRemote || gp.playerColor == color;
                  
                  return Text(
                    (isMyTurn && isLocalPlayerCard) ? 'YOUR TURN' : 'ACTING...',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.textPrimary,
                          fontWeight: FontWeight.bold,
                        ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildGameControls(BuildContext context, GameProvider gameProvider) {
    // Check for game over
    if (gameProvider.winner != null || gameProvider.isDraw) {
      return _buildGameOverDialog(context, gameProvider);
    }

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          // Bet Amount Display
          if (betAmount > 0)
            Expanded(
              child: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  gradient: AppColors.goldGradient,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.monetization_on, color: Colors.white),
                    const SizedBox(width: 8),
                    Text(
                      'Pot: KSH ${(betAmount * 2).toStringAsFixed(0)}',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                  ],
                ),
              ),
            ),
          const SizedBox(width: 12),

          // Reset Button
          ElevatedButton.icon(
            onPressed: () {
              _showResetDialog(context, gameProvider);
            },
            icon: const Icon(Icons.refresh),
            label: const Text('Reset'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGameOverDialog(BuildContext context, GameProvider gameProvider) {
    String title;
    String message;
    Color color;

    if (gameProvider.isDraw) {
      title = 'Draw!';
      message = 'The game ended in a draw';
      color = AppColors.warning;
    } else {
      final winnerName = gameProvider.winner == PieceColor.red
          ? (player1Name ?? 'Player 1')
          : (player2Name ?? 'Player 2');
      title = 'Game Over!';
      message = '$winnerName wins!';
      color = AppColors.success;
    }

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: color.withOpacity(0.2),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color, width: 2),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            title,
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                  color: color,
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 8),
          Text(
            message,
            style: Theme.of(context).textTheme.titleMedium,
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () {
                    gameProvider.resetGame();
                  },
                  icon: const Icon(Icons.refresh),
                  label: const Text('Play Again'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pop(context);
                  },
                  icon: const Icon(Icons.home),
                  label: const Text('Home'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.secondary,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _showResetDialog(BuildContext context, GameProvider gameProvider) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        title: const Text('Reset Game?'),
        content: const Text('Are you sure you want to reset the game?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              gameProvider.resetGame();
              Navigator.pop(context);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
            ),
            child: const Text('Reset'),
          ),
        ],
      ),
    );
  }

  Widget _buildGameCodeHeader(BuildContext context, String code) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.accent.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.accent.withOpacity(0.5)),
      ),
      child: Column(
        children: [
          Text(
            'SHARE THIS CODE',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: AppColors.accent,
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                code,
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      color: AppColors.textPrimary,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 4,
                    ),
              ),
              const SizedBox(width: 12),
              IconButton(
                onPressed: () {
                  // Copy to clipboard
                },
                icon: const Icon(Icons.copy, color: AppColors.accent),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildWaitingStatus() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const SizedBox(
            width: 16,
            height: 16,
            child: CircularProgressIndicator(strokeWidth: 2),
          ),
          const SizedBox(width: 12),
          Text(
            'Waiting for opponent to join...',
            style: TextStyle(color: AppColors.textSecondary),
          ),
        ],
      ),
    );
  }
}
