import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/game_provider.dart';
import '../../models/draught_models.dart';
import 'draught_piece.dart';

class DraughtBoardWidget extends StatelessWidget {
  const DraughtBoardWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<GameProvider>(
      builder: (context, gameProvider, child) {
        return Container(
          decoration: BoxDecoration(
            border: Border.all(color: AppColors.boardBorder, width: 8),
            borderRadius: BorderRadius.circular(8),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.5),
                blurRadius: 15,
                spreadRadius: 5,
              ),
            ],
          ),
          child: AspectRatio(
            aspectRatio: 1,
            child: GridView.builder(
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 8,
              ),
              itemCount: 64,
              itemBuilder: (context, index) {
                final row = index ~/ 8;
                final col = index % 8;
                final isDark = (row + col) % 2 == 1;
                final piece = gameProvider.board[row][col];
                
                // Check if this square is a valid move for the selected piece
                final isValidMove = gameProvider.validMoves.any(
                    (move) => move.to.row == row && move.to.col == col);
                
                final isSelected = gameProvider.selectedPiece?.position.row == row &&
                                  gameProvider.selectedPiece?.position.col == col;

                return GestureDetector(
                  onTap: () {
                    if (piece != null && piece.color == gameProvider.currentTurn) {
                      gameProvider.selectPiece(row, col);
                    } else if (isValidMove) {
                      gameProvider.movePiece(row, col);
                    } else {
                      gameProvider.deselectPiece();
                    }
                  },
                  child: Container(
                    decoration: BoxDecoration(
                      color: isDark ? AppColors.boardDark : AppColors.boardLight,
                    ),
                    child: Stack(
                      children: [
                        // Move Indicator
                        if (isValidMove)
                          Center(
                            child: Container(
                              width: 20,
                              height: 20,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: AppColors.validMove.withOpacity(0.5),
                              ),
                            ),
                          ),
                        // Piece
                        if (piece != null)
                          Center(
                            child: DraughtPieceWidget(
                              piece: piece,
                              isSelected: isSelected,
                            ),
                          ),
                        // Row/Col labels (Optional, for debugging or pro feel)
                        if (col == 0)
                          Positioned(
                            left: 2,
                            top: 2,
                            child: Text(
                              '${8 - row}',
                              style: TextStyle(
                                fontSize: 10,
                                color: isDark ? AppColors.boardLight.withOpacity(0.5) : AppColors.boardDark.withOpacity(0.5),
                              ),
                            ),
                          ),
                        if (row == 7)
                          Positioned(
                            right: 2,
                            bottom: 2,
                            child: Text(
                              String.fromCharCode(97 + col),
                              style: TextStyle(
                                fontSize: 10,
                                color: isDark ? AppColors.boardLight.withOpacity(0.5) : AppColors.boardDark.withOpacity(0.5),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        );
      },
    );
  }
}
