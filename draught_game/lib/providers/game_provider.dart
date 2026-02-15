import 'dart:convert';
import 'package:flutter/foundation.dart';
import '../models/draught_models.dart';
import '../services/api_service.dart';
import '../services/pusher_service.dart';

class GameProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  final PusherService _pusherService = PusherService();
  int? remoteGameId;
  String? remoteGameCode;
  PieceColor? playerColor; // The color assigned to the current user

  // 8x8 board, null means empty square
  List<List<DraughtPiece?>> board = List.generate(
    8,
    (row) => List.generate(8, (col) => null),
  );

  PieceColor currentTurn = PieceColor.red;
  DraughtPiece? selectedPiece;
  List<Move> validMoves = [];
  List<Move> moveHistory = [];
  
  // Game state
  bool gameStarted = false;
  PieceColor? winner;
  bool isDraw = false;
  bool isRemote = false;

  GameProvider() {
    initializeBoard();
  }

  Future<void> createRemoteGame(double betAmount) async {
    try {
      final response = await _apiService.createGame(betAmount);
      remoteGameId = response['id'];
      remoteGameCode = response['game_code'];
      playerColor = PieceColor.red; // Creator is red
      isRemote = true;
      initializeBoard();
      _subscribeToGame();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> joinRemoteGame(String code) async {
    try {
      final response = await _apiService.joinGame(code);
      remoteGameId = response['id'];
      remoteGameCode = response['game_code'];
      playerColor = PieceColor.black; // Joiner is black
      isRemote = true;
      
      _updateBoardFromRemote(response['board_state']);
      currentTurn = response['current_turn'] == 'red' ? PieceColor.red : PieceColor.black;
      gameStarted = response['status'] == 'in_progress';
      notifyListeners();
      _subscribeToGame();
    } catch (e) {
      rethrow;
    }
  }

  void _subscribeToGame() {
    if (remoteGameId == null) return;
    _pusherService.subscribeToGame(remoteGameId!, (event) {
      _handleRemoteEvent(event);
    });
  }

  void _handleRemoteEvent(dynamic event) {
    // Handling dart_pusher_channels event structure
    final dynamic rawData = event.data;
    final Map<String, dynamic> data = rawData is String ? jsonDecode(rawData) : rawData;
    final String eventName = event.name ?? '';
    
    switch (eventName) {
      case 'move.made':
        if (data['board_state'] != null) {
          _updateBoardFromRemote(data['board_state']);
        }
        currentTurn = data['current_turn'] == 'red' ? PieceColor.red : PieceColor.black;
        notifyListeners();
        break;
      case 'game.joined':
        gameStarted = true;
        notifyListeners();
        break;
      case 'game.ended':
        gameStarted = false;
        notifyListeners();
        break;
    }
  }

  void _updateBoardFromRemote(List<dynamic> remoteBoard) {
    for (int r = 0; r < 8; r++) {
      for (int c = 0; c < 8; c++) {
        final cell = remoteBoard[r][c];
        if (cell == null) {
          board[r][c] = null;
        } else {
          board[r][c] = DraughtPiece(
            color: cell['color'] == 'red' ? PieceColor.red : PieceColor.black,
            type: cell['type'] == 'king' ? PieceType.king : PieceType.normal,
            position: Position(r, c),
          );
        }
      }
    }
  }

  void leaveGame() {
    if (isRemote) {
      _pusherService.disconnect();
    }
    remoteGameId = null;
    remoteGameCode = null;
    isRemote = false;
    gameStarted = false;
    notifyListeners();
  }

  void initializeBoard() {
    // Clear board
    board = List.generate(8, (row) => List.generate(8, (col) => null));

    // Place black pieces (top 3 rows)
    for (int row = 0; row < 3; row++) {
      for (int col = 0; col < 8; col++) {
        if ((row + col) % 2 == 1) {
          board[row][col] = DraughtPiece(
            color: PieceColor.black,
            type: PieceType.normal,
            position: Position(row, col),
          );
        }
      }
    }

    // Place red pieces (bottom 3 rows)
    for (int row = 5; row < 8; row++) {
      for (int col = 0; col < 8; col++) {
        if ((row + col) % 2 == 1) {
          board[row][col] = DraughtPiece(
            color: PieceColor.red,
            type: PieceType.normal,
            position: Position(row, col),
          );
        }
      }
    }

    currentTurn = PieceColor.red;
    selectedPiece = null;
    validMoves = [];
    moveHistory = [];
    gameStarted = true;
    winner = null;
    isDraw = false;
    notifyListeners();
  }

  void selectPiece(int row, int col) {
    // If it's a remote game, ensure it's the current player's turn to move their own color
    if (isRemote && currentTurn != playerColor) {
      return;
    }

    final piece = board[row][col];

    // Can't select empty square or opponent's piece
    if (piece == null || piece.color != currentTurn) {
      return;
    }

    selectedPiece = piece;
    validMoves = getValidMovesForPiece(piece);
    notifyListeners();
  }

  void deselectPiece() {
    selectedPiece = null;
    validMoves = [];
    notifyListeners();
  }

  bool movePiece(int toRow, int toCol) {
    if (selectedPiece == null) return false;

    final targetPosition = Position(toRow, toCol);
    final validMove = validMoves.firstWhere(
      (move) => move.to == targetPosition,
      orElse: () => Move(from: Position(-1, -1), to: Position(-1, -1)),
    );

    if (validMove.from.row == -1) return false;

    // Send move to API if remote
    if (isRemote && remoteGameId != null) {
      _apiService.makeMove(
        gameId: remoteGameId!,
        from: {'row': validMove.from.row, 'col': validMove.from.col},
        to: {'row': validMove.to.row, 'col': validMove.to.col},
        captured: validMove.capturedPositions.map((p) => {'row': p.row, 'col': p.col}).toList(),
        isKingPromotion: shouldPromote(selectedPiece!.copyWith(position: targetPosition)),
      ).catchError((e) {
        // Handle error, maybe revert local move?
        print('Remote move failed: $e');
      });
    }

    // Execute move locally for immediate feedback
    final fromRow = selectedPiece!.position.row;
    final fromCol = selectedPiece!.position.col;

    // Move piece
    board[toRow][toCol] = selectedPiece!.copyWith(
      position: Position(toRow, toCol),
    );
    board[fromRow][fromCol] = null;

    // Remove captured pieces
    for (var capturedPos in validMove.capturedPositions) {
      board[capturedPos.row][capturedPos.col] = null;
    }

    // Check for king promotion
    if (shouldPromote(board[toRow][toCol]!)) {
      board[toRow][toCol] = board[toRow][toCol]!.promote();
    }

    // Add to move history
    moveHistory.add(validMove);

    // Check for multi-jump
    final piece = board[toRow][toCol]!;
    final additionalCaptures = getValidMovesForPiece(piece)
        .where((move) => move.isCapture)
        .toList();

    if (validMove.isCapture && additionalCaptures.isNotEmpty) {
      // Player can continue capturing
      selectedPiece = piece;
      validMoves = additionalCaptures;
      notifyListeners();
      return true;
    }

    // Switch turn
    selectedPiece = null;
    validMoves = [];
    currentTurn =
        currentTurn == PieceColor.red ? PieceColor.black : PieceColor.red;

    // Check for game over
    checkGameOver();

    notifyListeners();
    return true;
  }

  List<Move> getValidMovesForPiece(DraughtPiece piece) {
    List<Move> moves = [];

    // Check for captures first (mandatory in draughts)
    List<Move> captures = getCaptureMoves(piece);
    if (captures.isNotEmpty) {
      return captures;
    }

    // Regular moves
    moves.addAll(getRegularMoves(piece));

    return moves;
  }

  List<Move> getRegularMoves(DraughtPiece piece) {
    List<Move> moves = [];
    final row = piece.position.row;
    final col = piece.position.col;

    // Direction based on piece color and type
    List<List<int>> directions = [];

    if (piece.type == PieceType.king) {
      // Kings can move in all diagonal directions
      directions = [
        [-1, -1], [-1, 1], // Up-left, Up-right
        [1, -1], [1, 1], // Down-left, Down-right
      ];
    } else {
      // Normal pieces move forward only
      if (piece.color == PieceColor.red) {
        directions = [[-1, -1], [-1, 1]]; // Up-left, Up-right
      } else {
        directions = [[1, -1], [1, 1]]; // Down-left, Down-right
      }
    }

    for (var dir in directions) {
      final newRow = row + dir[0];
      final newCol = col + dir[1];

      if (isValidPosition(newRow, newCol) && board[newRow][newCol] == null) {
        moves.add(Move(
          from: piece.position,
          to: Position(newRow, newCol),
        ));
      }
    }

    return moves;
  }

  List<Move> getCaptureMoves(DraughtPiece piece) {
    List<Move> captures = [];
    final row = piece.position.row;
    final col = piece.position.col;

    // All diagonal directions for capture checking
    List<List<int>> directions = [
      [-1, -1], [-1, 1], // Up-left, Up-right
      [1, -1], [1, 1], // Down-left, Down-right
    ];

    // For normal pieces, filter directions based on color
    if (piece.type == PieceType.normal) {
      if (piece.color == PieceColor.red) {
        directions = [[-1, -1], [-1, 1]]; // Can only capture forward
      } else {
        directions = [[1, -1], [1, 1]];
      }
    }

    for (var dir in directions) {
      final jumpRow = row + dir[0];
      final jumpCol = col + dir[1];
      final landRow = row + dir[0] * 2;
      final landCol = col + dir[1] * 2;

      if (isValidPosition(jumpRow, jumpCol) &&
          isValidPosition(landRow, landCol)) {
        final jumpPiece = board[jumpRow][jumpCol];
        final landSquare = board[landRow][landCol];

        // Can capture if there's an opponent piece and landing square is empty
        if (jumpPiece != null &&
            jumpPiece.color != piece.color &&
            landSquare == null) {
          captures.add(Move(
            from: piece.position,
            to: Position(landRow, landCol),
            capturedPositions: [Position(jumpRow, jumpCol)],
          ));
        }
      }
    }

    return captures;
  }

  bool isValidPosition(int row, int col) {
    return row >= 0 && row < 8 && col >= 0 && col < 8;
  }

  bool shouldPromote(DraughtPiece piece) {
    if (piece.type == PieceType.king) return false;

    // Red pieces promote at row 0, black pieces at row 7
    if (piece.color == PieceColor.red && piece.position.row == 0) {
      return true;
    }
    if (piece.color == PieceColor.black && piece.position.row == 7) {
      return true;
    }

    return false;
  }

  void checkGameOver() {
    // Count pieces and check for valid moves
    int redPieces = 0;
    int blackPieces = 0;
    bool redHasMoves = false;
    bool blackHasMoves = false;

    for (var row in board) {
      for (var piece in row) {
        if (piece != null) {
          if (piece.color == PieceColor.red) {
            redPieces++;
            if (getValidMovesForPiece(piece).isNotEmpty) {
              redHasMoves = true;
            }
          } else {
            blackPieces++;
            if (getValidMovesForPiece(piece).isNotEmpty) {
              blackHasMoves = true;
            }
          }
        }
      }
    }

    // Check win conditions
    if (redPieces == 0 || !redHasMoves) {
      winner = PieceColor.black;
      gameStarted = false;
    } else if (blackPieces == 0 || !blackHasMoves) {
      winner = PieceColor.red;
      gameStarted = false;
    }

    // Check for draw (50 moves without capture - simplified)
    if (moveHistory.length > 100) {
      final recentMoves = moveHistory.sublist(moveHistory.length - 50);
      if (recentMoves.every((move) => !move.isCapture)) {
        isDraw = true;
        gameStarted = false;
      }
    }
  }

  void resetGame() {
    initializeBoard();
  }

  // Get piece count for UI
  int getPieceCount(PieceColor color) {
    int count = 0;
    for (var row in board) {
      for (var piece in row) {
        if (piece != null && piece.color == color) {
          count++;
        }
      }
    }
    return count;
  }
}
