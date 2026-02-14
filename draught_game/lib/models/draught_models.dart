enum PieceColor { red, black }

enum PieceType { normal, king }

class Position {
  final int row;
  final int col;

  const Position(this.row, this.col);

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is Position &&
          runtimeType == other.runtimeType &&
          row == other.row &&
          col == other.col;

  @override
  int get hashCode => row.hashCode ^ col.hashCode;

  @override
  String toString() => 'Position($row, $col)';
}

class DraughtPiece {
  final PieceColor color;
  final PieceType type;
  final Position position;

  DraughtPiece({
    required this.color,
    required this.type,
    required this.position,
  });

  DraughtPiece copyWith({
    PieceColor? color,
    PieceType? type,
    Position? position,
  }) {
    return DraughtPiece(
      color: color ?? this.color,
      type: type ?? this.type,
      position: position ?? this.position,
    );
  }

  // Promote to king
  DraughtPiece promote() {
    return copyWith(type: PieceType.king);
  }

  @override
  String toString() =>
      'DraughtPiece(color: $color, type: $type, position: $position)';
}

class Move {
  final Position from;
  final Position to;
  final List<Position> capturedPositions;
  final bool isCapture;

  Move({
    required this.from,
    required this.to,
    this.capturedPositions = const [],
  }) : isCapture = capturedPositions.isNotEmpty;

  @override
  String toString() =>
      'Move(from: $from, to: $to, captures: $capturedPositions)';
}
