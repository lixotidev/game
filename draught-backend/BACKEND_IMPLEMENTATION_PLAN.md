# Laravel Backend Implementation Plan

## 📋 Overview
Complete Laravel backend for the Draught Betting Game with Paystack integration, real-time gameplay via Pusher/Reverb, and Filament admin dashboard.

## 🗄️ Database Schema

### Tables

#### 1. **users**
```sql
- id (bigint, primary)
- name (string)
- email (string, unique)
- phone (string, unique, nullable)
- email_verified_at (timestamp, nullable)
- password (string)
- avatar (string, nullable)
- wallet_balance (decimal(10,2), default: 0.00)
- total_games_played (integer, default: 0)
- total_wins (integer, default: 0)
- total_losses (integer, default: 0)
- total_ties (integer, default: 0)
- total_earnings (decimal(10,2), default: 0.00)
- is_active (boolean, default: true)
- remember_token
- timestamps
- soft_deletes
```

#### 2. **games**
```sql
- id (bigint, primary)
- game_code (string, unique) - 6 char code
- creator_id (foreign -> users.id)
- opponent_id (foreign -> users.id, nullable)
- status (enum: waiting, in_progress, completed, cancelled)
- bet_amount (decimal(10,2))
- total_pot (decimal(10,2))
- admin_commission (decimal(10,2))
- prize_amount (decimal(10,2))
- creator_color (enum: red, black)
- opponent_color (enum: red, black, nullable)
- current_turn (enum: red, black, nullable)
- winner_id (foreign -> users.id, nullable)
- result (enum: win, tie, cancelled, nullable)
- board_state (json) - 8x8 array
- started_at (timestamp, nullable)
- completed_at (timestamp, nullable)
- timestamps
```

#### 3. **game_moves**
```sql
- id (bigint, primary)
- game_id (foreign -> games.id)
- player_id (foreign -> users.id)
- move_number (integer)
- from_row (integer)
- from_col (integer)
- to_row (integer)
- to_col (integer)
- captured_positions (json, nullable)
- is_king_promotion (boolean, default: false)
- board_state_after (json)
- timestamps
```

#### 4. **transactions**
```sql
- id (bigint, primary)
- user_id (foreign -> users.id)
- type (enum: deposit, withdrawal, bet_placed, bet_won, bet_refund, commission)
- amount (decimal(10,2))
- status (enum: pending, completed, failed)
- paystack_reference (string, nullable, unique)
- paystack_access_code (string, nullable)
- game_id (foreign -> games.id, nullable)
- description (text, nullable)
- metadata (json, nullable)
- completed_at (timestamp, nullable)
- timestamps
```

#### 5. **withdrawals**
```sql
- id (bigint, primary)
- user_id (foreign -> users.id)
- amount (decimal(10,2))
- account_number (string)
- bank_code (string)
- account_name (string)
- status (enum: pending, processing, completed, failed)
- paystack_transfer_code (string, nullable)
- paystack_recipient_code (string, nullable)
- admin_notes (text, nullable)
- processed_by (foreign -> users.id, nullable)
- processed_at (timestamp, nullable)
- timestamps
```

## 🛣️ API Endpoints

### Authentication
```
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/user
POST   /api/update-profile
POST   /api/upload-avatar
```

### Wallet
```
GET    /api/wallet/balance
GET    /api/wallet/transactions
POST   /api/wallet/deposit/initialize
POST   /api/wallet/deposit/verify
POST   /api/wallet/withdraw/request
GET    /api/wallet/withdraw/banks
```

### Games
```
GET    /api/games/lobby           # List available games
POST   /api/games/create          # Create new game
POST   /api/games/{code}/join     # Join game by code
GET    /api/games/{id}            # Get game details
POST   /api/games/{id}/move       # Make a move
POST   /api/games/{id}/resign     # Resign from game
GET    /api/games/my-games        # User's game history
```

### Leaderboard
```
GET    /api/leaderboard/top-players
GET    /api/leaderboard/top-earners
```

### Webhooks
```
POST   /webhooks/paystack         # Paystack payment webhook
```

## 🎮 Game Logic Flow

### 1. Create Game
```
1. User initiates game creation with bet amount
2. Validate user has sufficient balance
3. Deduct bet amount from wallet
4. Create game record (status: waiting)
5. Create transaction (type: bet_placed)
6. Generate unique game code
7. Broadcast to lobby (new game available)
8. Return game details
```

### 2. Join Game
```
1. User requests to join game by code
2. Validate game exists and is waiting
3. Validate user has sufficient balance
4. Deduct bet amount from wallet
5. Update game (add opponent, status: in_progress)
6. Create transaction (type: bet_placed)
7. Calculate pot and commission
8. Initialize board state
9. Broadcast game start to both players
10. Return game details
```

### 3. Make Move
```
1. Receive move from player
2. Validate it's player's turn
3. Validate move is legal (server-side validation)
4. Update board state
5. Save move to game_moves table
6. Check for captures
7. Check for king promotion
8. Check for win/tie conditions
9. If game over:
   - Process payouts
   - Update statistics
   - Broadcast game end
10. Else:
   - Switch turn
   - Broadcast move to opponent
11. Return updated game state
```

### 4. Game Completion & Payout
```
1. Determine winner or tie
2. Calculate payouts:
   - Admin commission: 25% of pot
   - Winner: 75% of pot
   - Tie: 37.5% each
3. Update user wallets
4. Create payout transactions
5. Update game statistics
6. Update game status (completed)
7. Broadcast game result
8. Send notifications
```

## 🔐 Security Measures

### Move Validation
- All moves validated server-side
- Board state stored server-side
- Client cannot manipulate game state
- Move history logged

### Payment Security
- Paystack webhook signature verification
- Transaction idempotency
- Double-spending prevention
- Balance locks during transactions

### Anti-Cheat
- Server-side game logic
- Move timing validation
- Disconnect handling
- Suspicious activity logging

## 📡 Real-time Events (Pusher/Reverb)

### Channels
```
- lobby                    # Public lobby updates
- game.{gameId}           # Private game channel
- user.{userId}           # Private user notifications
```

### Events
```
- GameCreated             # New game in lobby
- GameJoined              # Opponent joined
- GameStarted             # Game begins
- MoveMade                # Player made move
- GameEnded               # Game completed
- WalletUpdated           # Balance changed
```

## 🎨 Filament Admin Dashboard

### Resources
1. **Users**
   - List, view, edit users
   - View wallet balance
   - View game history
   - Ban/suspend users

2. **Games**
   - List all games
   - View game details
   - View move history
   - Cancel games (refund bets)

3. **Transactions**
   - List all transactions
   - Filter by type, status
   - Export reports

4. **Withdrawals**
   - Pending withdrawals
   - Approve/reject
   - Process payouts

5. **Dashboard Widgets**
   - Total users
   - Active games
   - Total revenue (commission)
   - Pending withdrawals
   - Today's transactions

## 💳 Paystack Integration

### Deposit Flow
```
1. User initiates deposit
2. Call Paystack Initialize Transaction API
3. Return payment URL to user
4. User completes payment
5. Paystack sends webhook
6. Verify transaction
7. Credit user wallet
8. Update transaction status
```

### Withdrawal Flow
```
1. User requests withdrawal
2. Validate sufficient balance
3. Create withdrawal record
4. Admin reviews (optional)
5. Create Paystack transfer recipient
6. Initiate transfer
7. Deduct from wallet
8. Update withdrawal status
```

## 🚀 Deployment (cPanel)

### Requirements
- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js (for Reverb)

### Setup Steps
1. Upload Laravel files
2. Configure .env
3. Run migrations
4. Set up cron jobs
5. Configure Pusher/Reverb
6. Set up SSL
7. Configure webhooks

### Cron Jobs
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 📊 Performance Optimization

- Database indexing
- Query optimization
- Redis caching (if available)
- API rate limiting
- Response caching
- Eager loading relationships

## 🧪 Testing

- Unit tests for game logic
- Feature tests for API endpoints
- Payment integration tests
- Real-time event tests

## 📝 Environment Variables

```env
APP_NAME="Draught Game"
APP_URL=https://yourdomain.com

DB_DATABASE=draught_game
DB_USERNAME=your_username
DB_PASSWORD=your_password

PAYSTACK_PUBLIC_KEY=pk_test_xxx
PAYSTACK_SECRET_KEY=sk_test_xxx
PAYSTACK_MERCHANT_EMAIL=your@email.com

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

ADMIN_COMMISSION_PERCENTAGE=25
```

## 🎯 Implementation Order

1. ✅ Install Laravel & packages
2. ⏳ Create migrations
3. ⏳ Create models
4. ⏳ Build authentication
5. ⏳ Build wallet system
6. ⏳ Build game logic
7. ⏳ Integrate Paystack
8. ⏳ Setup real-time events
9. ⏳ Build admin dashboard
10. ⏳ Testing & deployment
