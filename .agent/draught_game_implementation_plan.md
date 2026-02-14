# Draught Betting Game - Implementation Plan

## 🎮 Project Overview
A multiplayer draught (checkers) game with real-money betting using Paystack payment integration.

## 💰 Business Logic
- Players deposit money via Paystack
- Player creates game with bet amount (e.g., 100 KSH)
- Another player joins with matching bet (100 KSH)
- Total pot: 200 KSH
- **Admin commission**: 25% (50 KSH)
- **Winner receives**: 75% (150 KSH)
- **Tie scenario**: Each player gets 37.5% (75 KSH each)

---

## 🏗️ Tech Stack

### Frontend
- **Flutter** (Dart)
- **State Management**: Riverpod or Provider
- **UI Components**: Custom widgets with premium design

### Backend
- **Firebase Authentication** (Phone/Email)
- **Cloud Firestore** (User data, game states, transactions)
- **Firebase Realtime Database** (Live game moves)
- **Cloud Functions** (Game logic, payouts, webhooks)

### Payment
- **Paystack API** (Deposits & Withdrawals)
- **Webhook handling** (Payment verification)

### Additional Services
- **Firebase Cloud Messaging** (Push notifications)
- **Firebase Storage** (User avatars)

---

## 📁 Project Structure

```
draught_game/
├── lib/
│   ├── main.dart
│   ├── core/
│   │   ├── constants/
│   │   │   ├── colors.dart
│   │   │   ├── text_styles.dart
│   │   │   └── app_constants.dart
│   │   ├── routes/
│   │   │   └── app_router.dart
│   │   ├── services/
│   │   │   ├── firebase_service.dart
│   │   │   ├── paystack_service.dart
│   │   │   └── game_service.dart
│   │   └── utils/
│   │       ├── validators.dart
│   │       └── helpers.dart
│   ├── models/
│   │   ├── user_model.dart
│   │   ├── game_model.dart
│   │   ├── transaction_model.dart
│   │   └── move_model.dart
│   ├── providers/
│   │   ├── auth_provider.dart
│   │   ├── wallet_provider.dart
│   │   ├── game_provider.dart
│   │   └── lobby_provider.dart
│   ├── screens/
│   │   ├── splash/
│   │   ├── auth/
│   │   │   ├── login_screen.dart
│   │   │   └── register_screen.dart
│   │   ├── home/
│   │   │   └── home_screen.dart
│   │   ├── wallet/
│   │   │   ├── wallet_screen.dart
│   │   │   ├── deposit_screen.dart
│   │   │   └── withdraw_screen.dart
│   │   ├── lobby/
│   │   │   ├── game_lobby_screen.dart
│   │   │   └── create_game_screen.dart
│   │   ├── game/
│   │   │   ├── game_board_screen.dart
│   │   │   └── game_result_screen.dart
│   │   └── profile/
│   │       └── profile_screen.dart
│   └── widgets/
│       ├── common/
│       │   ├── custom_button.dart
│       │   ├── custom_text_field.dart
│       │   └── loading_indicator.dart
│       ├── game/
│       │   ├── draught_board.dart
│       │   ├── draught_piece.dart
│       │   └── game_timer.dart
│       └── wallet/
│           └── transaction_card.dart
├── firebase/
│   └── functions/
│       ├── index.js
│       ├── game_logic.js
│       ├── payment_handler.js
│       └── payout_handler.js
└── pubspec.yaml
```

---

## 🗄️ Database Schema

### Firestore Collections

#### 1. **users**
```json
{
  "userId": "string",
  "email": "string",
  "phoneNumber": "string",
  "displayName": "string",
  "avatarUrl": "string",
  "walletBalance": 0,
  "totalGamesPlayed": 0,
  "totalWins": 0,
  "totalLosses": 0,
  "totalTies": 0,
  "totalEarnings": 0,
  "createdAt": "timestamp",
  "updatedAt": "timestamp"
}
```

#### 2. **games**
```json
{
  "gameId": "string",
  "status": "waiting|in_progress|completed|cancelled",
  "betAmount": 100,
  "totalPot": 200,
  "adminCommission": 50,
  "prizeAmount": 150,
  "creatorId": "string",
  "creatorColor": "red",
  "opponentId": "string|null",
  "opponentColor": "black",
  "winnerId": "string|null",
  "result": "win|tie|cancelled",
  "currentTurn": "red|black",
  "boardState": "array",
  "moveHistory": "array",
  "createdAt": "timestamp",
  "startedAt": "timestamp|null",
  "completedAt": "timestamp|null"
}
```

#### 3. **transactions**
```json
{
  "transactionId": "string",
  "userId": "string",
  "type": "deposit|withdrawal|bet_placed|bet_won|bet_refund|commission",
  "amount": 100,
  "status": "pending|completed|failed",
  "paystackReference": "string",
  "gameId": "string|null",
  "description": "string",
  "createdAt": "timestamp",
  "completedAt": "timestamp|null"
}
```

#### 4. **game_moves** (Realtime Database)
```json
{
  "gameId": {
    "moves": [
      {
        "playerId": "string",
        "from": {"row": 0, "col": 0},
        "to": {"row": 1, "col": 1},
        "captured": [{"row": 0, "col": 1}],
        "timestamp": 1234567890
      }
    ]
  }
}
```

---

## 🎯 Development Phases

### **Phase 1: Project Setup & Authentication** (Days 1-2)
- [ ] Initialize Flutter project
- [ ] Setup Firebase (Auth, Firestore, Realtime DB)
- [ ] Configure Paystack SDK
- [ ] Implement authentication (Email/Phone)
- [ ] Create splash screen
- [ ] Build login/register screens
- [ ] Setup state management (Riverpod/Provider)

### **Phase 2: Wallet & Payment Integration** (Days 3-4)
- [ ] Build wallet screen (display balance)
- [ ] Implement Paystack deposit flow
- [ ] Create deposit screen with amount input
- [ ] Handle Paystack payment verification
- [ ] Implement withdrawal request system
- [ ] Create transaction history screen
- [ ] Setup Cloud Functions for payment webhooks

### **Phase 3: Game Lobby System** (Days 5-6)
- [ ] Build game lobby screen (list available games)
- [ ] Create game creation flow
- [ ] Implement bet amount validation (check wallet balance)
- [ ] Build "Join Game" functionality
- [ ] Add real-time lobby updates
- [ ] Implement game matching logic
- [ ] Create waiting room UI

### **Phase 4: Draught Game Logic** (Days 7-10)
- [ ] Design game board UI (8x8 grid)
- [ ] Create draught piece widgets
- [ ] Implement piece movement logic
- [ ] Add piece capture mechanics
- [ ] Implement king promotion
- [ ] Add move validation
- [ ] Build turn-based system
- [ ] Implement game timer (optional)
- [ ] Add real-time move synchronization

### **Phase 5: Game Completion & Payouts** (Days 11-12)
- [ ] Implement win detection logic
- [ ] Add tie/draw detection
- [ ] Build game result screen
- [ ] Create payout Cloud Function
- [ ] Calculate admin commission (25%)
- [ ] Distribute winnings to winner
- [ ] Handle tie payouts (split 37.5% each)
- [ ] Update user statistics
- [ ] Send push notifications for game results

### **Phase 6: User Profile & History** (Days 13-14)
- [ ] Build profile screen
- [ ] Display user statistics
- [ ] Show game history
- [ ] Implement avatar upload
- [ ] Add edit profile functionality
- [ ] Create leaderboard (optional)

### **Phase 7: Polish & Testing** (Days 15-16)
- [ ] Add loading states
- [ ] Implement error handling
- [ ] Add animations and transitions
- [ ] Test payment flows
- [ ] Test game logic edge cases
- [ ] Add sound effects (optional)
- [ ] Implement app settings
- [ ] Add terms & conditions

### **Phase 8: Deployment** (Day 17-18)
- [ ] Setup Firebase production environment
- [ ] Configure Paystack production keys
- [ ] Build release APK/IPA
- [ ] Test on physical devices
- [ ] Submit to Play Store/App Store
- [ ] Setup monitoring & analytics

---

## 🎨 UI/UX Design Principles

### Color Scheme
- **Primary**: Deep Green (#1B5E20) - Represents money/growth
- **Secondary**: Gold (#FFD700) - Premium feel
- **Accent**: Orange (#FF6F00) - Call-to-action
- **Background**: Dark theme with gradients
- **Board**: Classic wood texture or modern flat design

### Key Screens Design
1. **Home Screen**: Balance card, quick actions, recent games
2. **Lobby**: Grid/list of available games with bet amounts
3. **Game Board**: 8x8 draught board, player info, timer, chat
4. **Wallet**: Balance, deposit/withdraw buttons, transaction history

---

## 🔒 Security Considerations

1. **Payment Security**
   - Never store Paystack secret keys in app
   - Use Cloud Functions for sensitive operations
   - Verify all payments server-side

2. **Game Integrity**
   - Validate all moves server-side
   - Use Firestore security rules
   - Implement anti-cheat measures
   - Add timeout mechanisms

3. **User Data**
   - Encrypt sensitive data
   - Implement proper authentication
   - Add rate limiting
   - Validate all inputs

---

## 📊 Admin Dashboard (Future Enhancement)

- Monitor total games played
- Track commission earnings
- View user statistics
- Handle withdrawal requests
- Manage disputes
- View analytics

---

## 🚀 Launch Checklist

- [ ] Legal compliance (gambling regulations)
- [ ] Terms of service
- [ ] Privacy policy
- [ ] Age verification (18+)
- [ ] Responsible gaming features
- [ ] Customer support system
- [ ] Payment gateway testing
- [ ] Load testing
- [ ] Security audit

---

## 📈 Future Enhancements

1. **Social Features**
   - Friend system
   - Private games
   - In-game chat
   - Emojis/reactions

2. **Tournaments**
   - Scheduled tournaments
   - Bracket system
   - Larger prize pools

3. **Gamification**
   - Achievements
   - Badges
   - Daily rewards
   - Referral bonuses

4. **Advanced Features**
   - Replay system
   - AI opponent (practice mode)
   - Multiple game variants
   - Live streaming

---

## 💡 Key Implementation Notes

### Paystack Integration
```dart
// Initialize payment
final response = await PaystackService.initializeTransaction(
  amount: amount * 100, // Convert to kobo/cents
  email: user.email,
  reference: generateReference(),
);

// Verify payment
final verified = await PaystackService.verifyTransaction(reference);
if (verified) {
  // Update user wallet
  await updateWalletBalance(userId, amount);
}
```

### Game Payout Logic
```dart
// When game ends
final totalPot = game.betAmount * 2;
final adminCommission = totalPot * 0.25;
final prizeAmount = totalPot - adminCommission;

if (game.result == 'win') {
  // Winner gets 75%
  await updateWalletBalance(game.winnerId, prizeAmount);
} else if (game.result == 'tie') {
  // Each player gets 37.5%
  final splitAmount = prizeAmount / 2;
  await updateWalletBalance(game.creatorId, splitAmount);
  await updateWalletBalance(game.opponentId, splitAmount);
}

// Record admin commission
await recordCommission(game.gameId, adminCommission);
```

---

## 🎯 Success Metrics

- User registration rate
- Deposit conversion rate
- Average bet amount
- Games completed per day
- User retention rate
- Revenue (admin commission)
- Average session duration

---

**Estimated Timeline**: 18-20 days for MVP  
**Team Size**: 1-2 developers  
**Budget Considerations**: Firebase costs, Paystack fees (1.5% + 100 NGN per transaction)
