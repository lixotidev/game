# 🎮 Draught Betting Game

A beautiful, multiplayer draught (checkers) game with real-money betting functionality built with Flutter.

## ✨ Features

### Current Implementation (UI Phase)
- ✅ **Beautiful Premium UI** - Modern dark theme with gradients and animations
- ✅ **Complete Draught Game Logic** - Full implementation of draught rules
  - Regular piece movement
  - Mandatory captures
  - Multi-jump captures
  - King promotion
  - Win/draw detection
- ✅ **Game Lobby System** - Create and join games
- ✅ **Mock Wallet** - Balance display and deposit simulation
- ✅ **Betting Interface** - Set bet amounts and view pot
- ✅ **Player Profiles** - Avatar and statistics display
- ✅ **Animated Splash Screen** - Premium app branding

### Upcoming (Backend Phase)
- 🔄 **Firebase Integration**
  - User authentication
  - Real-time multiplayer
  - Cloud Firestore database
- 🔄 **Paystack Payment Integration**
  - Deposits
  - Withdrawals
  - Transaction history
- 🔄 **Payout System**
  - Automatic winner payouts (75% of pot)
  - Admin commission (25%)
  - Tie handling (37.5% each)

## 🎯 Game Rules

### Standard Draught Rules
1. **Movement**: Pieces move diagonally forward one square
2. **Captures**: Jump over opponent pieces to capture (mandatory)
3. **Multi-Jumps**: Continue capturing if possible in the same turn
4. **King Promotion**: Pieces reaching the opposite end become kings
5. **King Movement**: Kings can move backward and forward
6. **Win Conditions**: 
   - Capture all opponent pieces
   - Block opponent from making any moves

### Betting System
- Players create games with a bet amount
- Opponent joins with matching bet
- Total pot = Bet × 2
- **Winner receives**: 75% of pot
- **Admin commission**: 25% of pot
- **Tie scenario**: Each player gets 37.5% of pot

## 🚀 Getting Started

### Prerequisites
- Flutter SDK (3.10.8 or higher)
- Dart SDK
- Windows/macOS/Linux for development
- Android Studio or VS Code

### Installation

1. **Clone the repository**
   ```bash
   cd draught_game
   ```

2. **Install dependencies**
   ```bash
   flutter pub get
   ```

3. **Run the app**
   ```bash
   # For Windows
   flutter run -d windows
   
   # For Android
   flutter run -d android
   
   # For iOS
   flutter run -d ios
   ```

## 📱 App Structure

```
lib/
├── core/
│   └── constants/
│       ├── app_colors.dart      # Color scheme
│       └── app_theme.dart       # Material theme
├── models/
│   └── draught_models.dart      # Game models
├── providers/
│   └── game_provider.dart       # Game state management
├── screens/
│   ├── home/
│   │   └── home_screen.dart     # Lobby & wallet
│   └── game/
│       └── game_board_screen.dart # Game board
├── widgets/
│   └── game/
│       ├── draught_board.dart   # 8x8 board widget
│       └── draught_piece.dart   # Piece widget
└── main.dart                    # App entry point
```

## 🎨 Design

### Color Scheme
- **Primary**: Deep Green (#1B5E20) - Represents money/growth
- **Secondary**: Gold (#FFD700) - Premium feel
- **Accent**: Orange (#FF6F00) - Call-to-action
- **Background**: Dark theme with gradients

### Typography
- **Headings**: Poppins (Bold, modern)
- **Body**: Inter (Clean, readable)

## 🎮 How to Play

1. **Launch the app** - View your wallet balance on the home screen
2. **Create a game** - Tap "Create Game" and set your bet amount
3. **Join a game** - Browse available games and join one
4. **Play** - Take turns moving pieces
   - Tap a piece to select it
   - Tap a highlighted square to move
   - Captures are mandatory
5. **Win** - Capture all opponent pieces or block their moves
6. **Collect winnings** - Winner automatically receives 75% of the pot

## 🔧 Development

### State Management
- **Provider** - For game state and UI updates

### Key Components

#### GameProvider
Manages the entire game state:
- Board state (8×8 grid)
- Current turn
- Valid moves calculation
- Piece movement and captures
- Win/draw detection

#### DraughtBoardWidget
- Renders the 8×8 game board
- Handles user interactions
- Displays valid move indicators
- Shows piece positions

#### HomeScreen
- Wallet display
- Game lobby
- Create/join game functionality

## 📊 Game Statistics

The app tracks:
- Total games played
- Wins/Losses/Ties
- Total earnings
- Current wallet balance

## 🔐 Security (Backend Phase)

When backend is integrated:
- Server-side move validation
- Secure payment processing
- Encrypted user data
- Anti-cheat measures

## 🚧 Roadmap

### Phase 1: UI & Game Logic ✅ (Current)
- [x] Premium UI design
- [x] Complete draught game logic
- [x] Mock lobby system
- [x] Mock wallet

### Phase 2: Backend Integration (Next)
- [ ] Firebase setup
- [ ] User authentication
- [ ] Real-time multiplayer
- [ ] Paystack integration
- [ ] Payout system

### Phase 3: Enhancements
- [ ] In-game chat
- [ ] Friend system
- [ ] Tournaments
- [ ] Leaderboards
- [ ] Push notifications
- [ ] Sound effects

## 📄 License

This project is for educational and commercial purposes.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For issues or questions, please open an issue on GitHub.

---

**Built with ❤️ using Flutter**
