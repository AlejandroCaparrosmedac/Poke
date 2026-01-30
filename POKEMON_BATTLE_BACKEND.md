# Pokémon Battle Backend - Implementation Guide

## Overview

This is a complete Laravel backend implementation for a competitive Pokémon battle system with PVP and PVE support. The system delegates all battle logic to a Pokémon Showdown microservice running on port 9000.

## Architecture

### Core Components

#### 1. **Models**
- **User**: Authenticated user with teams and battle participation
- **Team**: Player's Pokémon teams (max 6 Pokémon per team)
- **Battle**: Represents a single battle (PVP or PVE)
- **BattlePlayer**: Tracks player participation in battles
- **TurnDecision**: Records player decisions during battles

#### 2. **Services**

**ShowdownClient** (`app/Services/ShowdownClient.php`)
- HTTP client communicating with Showdown microservice (localhost:9000)
- Methods:
  - `createBattle()` - Initialize battle on Showdown
  - `submitMove()` - Send player move to Showdown
  - `switchPokemon()` - Switch active Pokémon
  - `getBattleState()` - Get current battle state
  - `forfeitBattle()` - Concede battle
  - `getAvailableMoves()` - Retrieve Pokémon moves
  - `getPokemonStats()` - Get stat information

**MatchmakingService** (`app/Services/MatchmakingService.php`)
- Handles player matching for PVP battles
- Methods:
  - `findRandomOpponent()` - Find random opponent
  - `createPvpBattle()` - Initialize PVP battle
  - `calculateMatchDifficulty()` - Rating-based matching (future)

**PvEAIService** (`app/Services/PvEAIService.php`)
- Generates AI opponent and moves
- Methods:
  - `generateRandomMove()` - Random move selection
  - `generateIntelligentMove()` - Strategy-based moves
  - `shouldSwitch()` - Switch Pokémon logic
  - `createPvEBattle()` - Initialize PvE battle
  - `executeTurn()` - Execute AI turn

#### 3. **Controllers**

**BattleController** (`app/Http/Controllers/BattleController.php`)
- `index()` - List user's battles
- `show()` - Get battle details
- `createPvp()` - Create PVP battle
- `createPve()` - Create PvE battle
- `submitMove()` - Submit player move
- `switchPokemon()` - Switch Pokémon
- `getState()` - Get battle state
- `forfeit()` - Forfeit battle

**TeamController** (`app/Http/Controllers/TeamController.php`)
- `index()` - List user's teams
- `store()` - Create team
- `show()` - Get team details
- `update()` - Update team
- `destroy()` - Delete team

#### 4. **Events (Broadcasting)**
- **BattleStarted**: Notifies players when battle begins
- **TurnResolved**: Updates after each turn
- **BattleFinished**: Notifies when battle ends

#### 5. **Policies (Authorization)**
- **BattlePolicy**: Controls battle access
- **TeamPolicy**: Controls team access

## Database Schema

### Tables

**teams**
```sql
id, user_id, name, pokemon_data (JSON), timestamps
```

**battles**
```sql
id, type (pvp/pve), format (singles/doubles), status, showdown_id, 
showdown_room_id, winner_id, replay_log (JSON), timestamps
```

**battle_players**
```sql
id, battle_id, user_id (nullable), team_id, player_slot (p1/p2), 
is_ai, is_winner, current_turn, timestamps
```

**turn_decisions**
```sql
id, battle_id, battle_player_id, turn_number, decision_type (move/switch), 
decision_data (JSON), decision_result (JSON), status (pending/executed/failed), timestamps
```

## API Routes

### Teams (Protected)
```
GET    /api/teams                    - List teams
POST   /api/teams                    - Create team
GET    /api/teams/{team}             - Get team
PUT    /api/teams/{team}             - Update team
DELETE /api/teams/{team}             - Delete team
```

### Battles (Protected)
```
GET    /api/battles                  - List battles
POST   /api/battles/pvp              - Create PVP battle
POST   /api/battles/pve              - Create PvE battle
GET    /api/battles/{battle}         - Get battle
GET    /api/battles/{battle}/state   - Get battle state
POST   /api/battles/{battle}/move    - Submit move
POST   /api/battles/{battle}/switch  - Switch Pokémon
POST   /api/battles/{battle}/forfeit - Forfeit battle
```

## Setup Instructions

### 1. Environment Configuration

Update `.env`:
```env
SHOWDOWN_URL=http://localhost:9000
SHOWDOWN_TIMEOUT=30

BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-id
PUSHER_APP_KEY=your-key
PUSHER_APP_SECRET=your-secret
PUSHER_APP_CLUSTER=mt1
```

### 2. Install Dependencies

```bash
composer install
npm install
npm run build
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Start Services

**Terminal 1 - Laravel:**
```bash
php artisan serve
```

**Terminal 2 - Showdown Microservice:**
```bash
cd ../pokemon-showdown-master
$env:PORT=9000
npm start
```

**Terminal 3 - Queue Worker (optional):**
```bash
php artisan queue:listen
```

### 5. Test Broadcasting (optional)

For real-time updates, configure Pusher credentials or use a local broadcasting driver:

```env
BROADCAST_DRIVER=log  # Development: log to console
# or
BROADCAST_DRIVER=pusher  # Production: use Pusher
```

## WebSocket Events

### Event Flow

1. **Battle Creation** → `BattleStarted` event
2. **Each Turn** → `TurnResolved` event
3. **Battle End** → `BattleFinished` event

### Listening to Events (Frontend)

```javascript
// Subscribe to private user channel
Echo.private(`user.${userId}`)
    .listen('BattleStarted', (e) => console.log('Battle started:', e))
    .listen('TurnResolved', (e) => console.log('Turn resolved:', e))
    .listen('BattleFinished', (e) => console.log('Battle finished:', e));
```

## Key Design Decisions

### 1. **Separation of Concerns**
- Laravel handles: User management, team storage, state tracking, WebSocket orchestration
- Showdown handles: All Pokémon mechanics, damage calculations, move effects
- AI handles: Move selection only (no game logic)

### 2. **Database Normalization**
- Turn decisions are immutable records
- Battle state is stored in Showdown, replayed from turn log if needed
- JSON storage for flexible Pokémon data from PokéAPI

### 3. **Real-time Updates**
- WebSocket events notify players of important battle states
- Broadcasting ensures both players see consistent game state
- Private channels prevent information leaking between opponents

### 4. **PvE Implementation**
- AI is treated as a regular `BattlePlayer` with `is_ai = true`
- Moves are generated after player move to maintain turn order
- Simple difficulty system (easy/normal/hard) affects team composition

## Error Handling

All controllers include comprehensive error handling:
- Authorization checks prevent unauthorized access
- Try-catch blocks handle Showdown communication failures
- Validation ensures data integrity
- Detailed logging for debugging

## Future Enhancements

1. **Rating System**: Implement ELO for matchmaking
2. **Leaderboards**: Track win/loss statistics
3. **Replays**: Store and replay full battle sequences
4. **Tournaments**: Multi-battle tournament support
5. **Custom Rules**: Support for custom battle formats
6. **Spectating**: Allow users to watch live battles
7. **Chat**: In-battle messaging between opponents
8. **Achievements**: Badge system for accomplishments

## Dependencies

- **Laravel 12**: Web framework
- **Pusher**: Real-time broadcasting
- **Illuminate/Support**: Utility classes
- **SQLite**: Database (default)

## Configuration Files

- `config/broadcasting.php` - Broadcasting configuration
- `config/services.php` - Service credentials
- `.env` - Environment variables
- `routes/web.php` - API routes
- `app/Providers/AppServiceProvider.php` - Service registration

## Testing

Run unit tests:
```bash
php artisan test
```

Test API endpoints:
```bash
# Create team
POST http://localhost:8000/api/teams
{
  "name": "My Team",
  "pokemon_data": [
    {"name": "Pikachu", "level": 50},
    {"name": "Charizard", "level": 50}
  ]
}

# Create PVP battle
POST http://localhost:8000/api/battles/pvp
{
  "team_id": 1,
  "format": "singles"
}

# Submit move
POST http://localhost:8000/api/battles/1/move
{
  "move": "Thunderbolt"
}
```

## Troubleshooting

**Showdown Connection Error**: Ensure Showdown microservice is running on port 9000
```bash
# Check if running
netstat -ano | findstr :9000
```

**Broadcasting Not Working**: Verify BROADCAST_CONNECTION and Pusher credentials in .env

**Authorization Errors**: Ensure user is authenticated and policy allows action

**Database Lock**: Clear caches and reset migrations:
```bash
php artisan cache:clear
php artisan migrate:fresh --seed
```

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode: `APP_DEBUG=true`
3. Review Showdown microservice logs
4. Check WebSocket connection status
