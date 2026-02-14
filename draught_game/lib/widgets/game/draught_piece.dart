import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../models/draught_models.dart';

class DraughtPieceWidget extends StatelessWidget {
  final DraughtPiece piece;
  final bool isSelected;
  final bool canBeSelected;

  const DraughtPieceWidget({
    super.key,
    required this.piece,
    this.isSelected = false,
    this.canBeSelected = false,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      margin: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: piece.color == PieceColor.red ? AppColors.redPiece : AppColors.blackPiece,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.4),
            blurRadius: 4,
            offset: const Offset(2, 2),
          ),
          if (isSelected)
            BoxShadow(
              color: AppColors.selectedPiece.withOpacity(0.8),
              blurRadius: 10,
              spreadRadius: 4,
            ),
        ],
        border: Border.all(
          color: piece.color == PieceColor.red 
              ? AppColors.redPieceLight.withOpacity(0.5) 
              : AppColors.blackPieceLight.withOpacity(0.5),
          width: 2,
        ),
        gradient: RadialGradient(
          colors: piece.color == PieceColor.red
              ? [AppColors.redPieceLight, AppColors.redPiece]
              : [AppColors.blackPieceLight, AppColors.blackPiece],
          center: const Alignment(-0.3, -0.3),
          radius: 0.8,
        ),
      ),
      child: Stack(
        alignment: Alignment.center,
        children: [
          // Texture rings for a premium look
          Container(
            width: double.infinity,
            height: double.infinity,
            margin: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(
                color: Colors.white.withOpacity(0.1),
                width: 1,
              ),
            ),
          ),
          Container(
            width: double.infinity,
            height: double.infinity,
            margin: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(
                color: Colors.white.withOpacity(0.1),
                width: 1,
              ),
            ),
          ),
          // King crown icon
          if (piece.type == PieceType.king)
            const Icon(
              Icons.star,
              color: AppColors.secondary,
              size: 24,
            ),
        ],
      ),
    );
  }
}
