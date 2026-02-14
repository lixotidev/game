import 'package:flutter/material.dart';

class AppColors {
  // Primary Colors
  static const Color primary = Color(0xFF1B5E20); // Deep Green
  static const Color primaryLight = Color(0xFF4CAF50);
  static const Color primaryDark = Color(0xFF0D3D13);
  
  // Secondary Colors
  static const Color secondary = Color(0xFFFFD700); // Gold
  static const Color secondaryLight = Color(0xFFFFE54C);
  static const Color secondaryDark = Color(0xFFC7A600);
  
  // Accent
  static const Color accent = Color(0xFFFF6F00); // Orange
  static const Color accentLight = Color(0xFFFF9E40);
  static const Color accentDark = Color(0xFFC43E00);
  
  // Background
  static const Color background = Color(0xFF0A0E12);
  static const Color backgroundLight = Color(0xFF1A1F25);
  static const Color surface = Color(0xFF1E2329);
  static const Color surfaceLight = Color(0xFF2A3038);
  
  // Game Board
  static const Color boardLight = Color(0xFFF0D9B5); // Light squares
  static const Color boardDark = Color(0xFFB58863); // Dark squares
  static const Color boardBorder = Color(0xFF8B4513);
  
  // Pieces
  static const Color redPiece = Color(0xFFE53935);
  static const Color redPieceLight = Color(0xFFEF5350);
  static const Color blackPiece = Color(0xFF212121);
  static const Color blackPieceLight = Color(0xFF424242);
  
  // Highlights
  static const Color validMove = Color(0xFF4CAF50);
  static const Color selectedPiece = Color(0xFFFFEB3B);
  static const Color captureMove = Color(0xFFFF5252);
  
  // Text
  static const Color textPrimary = Color(0xFFFFFFFF);
  static const Color textSecondary = Color(0xFFB0B0B0);
  static const Color textDisabled = Color(0xFF666666);
  
  // Status
  static const Color success = Color(0xFF4CAF50);
  static const Color error = Color(0xFFF44336);
  static const Color warning = Color(0xFFFF9800);
  static const Color info = Color(0xFF2196F3);
  
  // Gradients
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [primary, primaryLight],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  
  static const LinearGradient goldGradient = LinearGradient(
    colors: [secondary, secondaryLight],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  
  static const LinearGradient backgroundGradient = LinearGradient(
    colors: [background, backgroundLight],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );
}
