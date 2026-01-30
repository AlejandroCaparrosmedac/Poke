# API Testing Guide - Pokémon Battle Backend

## Quick Start Testing

### 1. Authentication

First, create a user account or login:

```bash
# Register
POST http://localhost:8000/register
{
  "name": "Test Player",
  "email": "test@example.com",
  "password": "password",
  "password_confirmation": "password"
}

# Login
POST http://localhost:8000/login
{
  "email": "test@example.com",
  "password": "password"
}
```

### 2. Teams Management

#### Create a Team
```bash
POST http://localhost:8000/api/teams
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Water Team",
  "pokemon_data": [
    {
      "name": "Blastoise",
      "level": 50,
      "moves": ["Hydro Pump", "Ice Beam", "Earthquake", "Recover"]
    },
    {
      "name": "Lapras",
      "level": 50,
      "moves": ["Hydro Pump", "Ice Beam", "Thunderbolt", "Recover"]
    },
    {
      "name": "Gyarados",
      "level": 50,
      "moves": ["Waterfall", "Earthquake", "Stone Edge", "Dragon Dance"]
    },
    {
      "name": "Dragonite",
      "level": 50,
      "moves": ["Earthquake", "Outrage", "Extreme Speed", "Dragon Dance"]
    },
    {
      "name": "Mamoswine",
      "level": 50,
      "moves": ["Earthquake", "Ice Shard", "Stone Edge", "Stealth Rock"]
    },
    {
      "name": "Articuno",
      "level": 50,
      "moves": ["Hurricane", "Ice Beam", "Roost", "Toxic Spikes"]
    }
  ]
}
```

Expected Response (201):
```json
{
  "success": true,
  "message": "Team created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "name": "Water Team",
    "pokemon_data": [...],
    "created_at": "2025-01-30T...",
    "updated_at": "2025-01-30T..."
  }
}
```

#### List Teams
```bash
GET http://localhost:8000/api/teams
Authorization: Bearer {token}
```

#### Get Specific Team
```bash
GET http://localhost:8000/api/teams/1
Authorization: Bearer {token}
```

#### Update Team
```bash
PUT http://localhost:8000/api/teams/1
Authorization: Bearer {token}

{
  "name": "Updated Water Team"
}
```

#### Delete Team
```bash
DELETE http://localhost:8000/api/teams/1
Authorization: Bearer {token}
```

### 3. Battles

#### Create PVP Battle
```bash
POST http://localhost:8000/api/battles/pvp
Content-Type: application/json
Authorization: Bearer {token}

{
  "team_id": 1,
  "format": "singles"
}
```

Expected Response (201):
```json
{
  "success": true,
  "message": "Battle created successfully",
  "data": {
    "id": 1,
    "type": "pvp",
    "format": "singles",
    "status": "active",
    "showdown_id": "uuid-here",
    "showdown_room_id": "battle-12345",
    "winner_id": null,
    "players": [
      {
        "id": 1,
        "battle_id": 1,
        "user_id": 1,
        "player_slot": "p1",
        "is_ai": false,
        "user": {
          "id": 1,
          "name": "Test Player",
          "email": "test@example.com"
        }
      },
      {
        "id": 2,
        "battle_id": 1,
        "user_id": 2,
        "player_slot": "p2",
        "is_ai": false,
        "user": {
          "id": 2,
          "name": "Opponent",
          "email": "opponent@example.com"
        }
      }
    ]
  }
}
```

#### Create PVE Battle
```bash
POST http://localhost:8000/api/battles/pve
Content-Type: application/json
Authorization: Bearer {token}

{
  "team_id": 1,
  "difficulty": "normal"
}
```

Difficulty options: `easy`, `normal`, `hard`

#### List Battles
```bash
GET http://localhost:8000/api/battles
Authorization: Bearer {token}
```

#### Get Battle Details
```bash
GET http://localhost:8000/api/battles/1
Authorization: Bearer {token}
```

### 4. Battle Actions

#### Get Current Battle State
```bash
GET http://localhost:8000/api/battles/1/state
Authorization: Bearer {token}
```

Expected Response:
```json
{
  "success": true,
  "data": {
    "battleId": "uuid",
    "activePokemons": {
      "p1": {
        "name": "Blastoise",
        "hp": 100,
        "maxHp": 100,
        "status": "ok",
        "moves": [
          {"name": "Hydro Pump", "pp": 8},
          {"name": "Ice Beam", "pp": 8},
          {"name": "Earthquake", "pp": 8},
          {"name": "Recover", "pp": 8}
        ]
      },
      "p2": {...}
    },
    "teams": {
      "p1": [
        {"name": "Blastoise", "status": "ok", "hp": 100},
        {"name": "Lapras", "status": "ok", "hp": 100},
        ...
      ],
      "p2": [...]
    },
    "currentTurn": 1
  }
}
```

#### Submit a Move
```bash
POST http://localhost:8000/api/battles/1/move
Content-Type: application/json
Authorization: Bearer {token}

{
  "move": "Hydro Pump"
}
```

Expected Response:
```json
{
  "success": true,
  "message": "Move submitted successfully",
  "data": {
    "decision": {
      "id": 1,
      "battle_id": 1,
      "battle_player_id": 1,
      "turn_number": 1,
      "decision_type": "move",
      "decision_data": {"move": "Hydro Pump"},
      "decision_result": {...},
      "status": "executed"
    },
    "result": {...}
  }
}
```

#### Switch Pokémon
```bash
POST http://localhost:8000/api/battles/1/switch
Content-Type: application/json
Authorization: Bearer {token}

{
  "pokemon_index": 1
}
```

#### Forfeit Battle
```bash
POST http://localhost:8000/api/battles/1/forfeit
Authorization: Bearer {token}
```

Expected Response:
```json
{
  "success": true,
  "message": "Battle forfeited"
}
```

## Error Responses

### Unauthorized (401)
```json
{
  "message": "Unauthorized"
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Team does not belong to you"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "No opponents available"
}
```

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "pokemon_data": ["The pokemon data field is required."]
  }
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Failed to create battle: Connection refused"
}
```

## WebSocket Events (Broadcasting)

When using Pusher or real-time broadcasting, subscribe to receive events:

### Frontend Example (JavaScript with Echo)

```javascript
// Subscribe to private user channel
Echo.private(`user.${userId}`)
    .listen('BattleStarted', (event) => {
        console.log('Battle started!', event);
        // Update UI with battle start
    })
    .listen('TurnResolved', (event) => {
        console.log('Turn resolved!', event);
        // Update battle state UI
    })
    .listen('BattleFinished', (event) => {
        console.log('Battle finished!', event);
        // Show battle results
    });
```

### Event Payloads

#### BattleStarted
```json
{
  "battleId": 1,
  "type": "pvp",
  "format": "singles",
  "players": [
    {
      "id": 1,
      "user": {
        "id": 1,
        "name": "Test Player"
      },
      "player_slot": "p1"
    },
    {
      "id": 2,
      "user": {
        "id": 2,
        "name": "Opponent"
      },
      "player_slot": "p2"
    }
  ]
}
```

#### TurnResolved
```json
{
  "battleId": 1,
  "playerSlot": "p1",
  "playerName": "Test Player",
  "turnNumber": 1,
  "lastDecision": {
    "id": 1,
    "turn_number": 1,
    "decision_type": "move",
    "decision_data": {"move": "Hydro Pump"},
    "status": "executed"
  }
}
```

#### BattleFinished
```json
{
  "battleId": 1,
  "winnerId": 1,
  "winner": {
    "id": 1,
    "name": "Test Player",
    "email": "test@example.com"
  },
  "replayLog": {
    "turn_1": {...},
    "turn_2": {...}
  }
}
```

## Postman Collection

Import this into Postman for easy testing:

```json
{
  "info": {
    "name": "Pokémon Battle API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Teams",
      "item": [
        {
          "name": "Create Team",
          "request": {
            "method": "POST",
            "url": "{{base_url}}/api/teams"
          }
        },
        {
          "name": "List Teams",
          "request": {
            "method": "GET",
            "url": "{{base_url}}/api/teams"
          }
        }
      ]
    },
    {
      "name": "Battles",
      "item": [
        {
          "name": "Create PVP Battle",
          "request": {
            "method": "POST",
            "url": "{{base_url}}/api/battles/pvp"
          }
        },
        {
          "name": "Get Battle State",
          "request": {
            "method": "GET",
            "url": "{{base_url}}/api/battles/{{battle_id}}/state"
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000"
    },
    {
      "key": "battle_id",
      "value": "1"
    }
  ]
}
```

## Testing Scenarios

### Scenario 1: Complete PVP Battle Flow
1. Create two user accounts
2. Create teams for both users
3. User 1 creates PVP battle
4. Get battle state
5. Both users submit moves
6. Continue until one user forfeits or battle ends

### Scenario 2: PVE Battle Flow
1. Create user and team
2. Create PVE battle (easy difficulty)
3. Get battle state
4. Submit moves (AI responds automatically)
5. Battle finishes when one team is defeated

### Scenario 3: Error Handling
1. Try to access battle without authorization → 403
2. Try to submit move to non-existent battle → 404
3. Try to create team with invalid data → 422
4. Try to switch to invalid Pokémon → 400 from Showdown

## cURL Examples

### Create Team
```bash
curl -X POST http://localhost:8000/api/teams \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "Fire Team",
    "pokemon_data": [
      {"name": "Charizard", "level": 50, "moves": ["Flamethrower", "Dragon Claw"]}
    ]
  }'
```

### Create PVP Battle
```bash
curl -X POST http://localhost:8000/api/battles/pvp \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "team_id": 1,
    "format": "singles"
  }'
```

### Submit Move
```bash
curl -X POST http://localhost:8000/api/battles/1/move \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "move": "Flamethrower"
  }'
```
