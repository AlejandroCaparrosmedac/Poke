# Integración Pokémon Showdown Microservice - Guía de Configuración

## 📍 Ubicación del Servicio

El servicio Pokémon Showdown Microservice está ubicado en:

```
pokemon-showdown-master/
├── battle-server.js          ← Servidor Express.js
├── MICROSERVICE.md           ← Documentación API
├── SETUP-MICROSERVICE.md     ← Guía de instalación
└── node_modules/             ← Dependencias
```

**Puerto**: `9000`
**URL**: `http://localhost:9000`

---

## 📦 Clientes Laravel

En el proyecto Laravel tienes **dos opciones de cliente**:

### 1. **PokemonBattleClient.php** ✅ RECOMENDADO
```php
app/Services/PokemonBattleClient.php
```
- Cliente optimizado para el microservicio real
- Usa endpoints HTTP correctos
- Manejo de errores robusto
- Métodos helper para construir equipos

**Uso:**
```php
$client = new PokemonBattleClient('http://localhost:9000');
$battleId = $client->createBattle(
    'gen9customgame',
    $p1Team,
    'Player1',
    $p2Team,
    'Player2'
);
```

### 2. **ShowdownIntegration.php** ✅ WRAPPER RECOMENDADO
```php
app/Services/ShowdownIntegration.php
```
- Wrapper que integra `PokemonBattleClient` con los modelos Laravel
- Abstrae la complejidad del formato Showdown
- Sincroniza datos con la base de datos

**Uso:**
```php
$integration = app(\App\Services\ShowdownIntegration::class);

// Crear batalla
$battleId = $integration->createBattle($battle, $p1, $p2);

// Enviar turno
$integration->submitTurn($battle, $p1, $p2, '>move 1', '>move 2');

// Ver estado
$state = $integration->getBattleState($battle);
```

### 3. **ShowdownClient.php** ⚠️ DEPRECADO
```php
app/Services/showdownClient.php
```
- Versión antigua, NO usa el formato correcto del microservicio
- Mantiene compatibilidad hacia atrás
- No usar en código nuevo

---

## 🔧 Configuración

### 1. Variables de Entorno (.env)
```env
# Servicio Pokémon Showdown
SHOWDOWN_URL=http://localhost:9000
SHOWDOWN_TIMEOUT=30

# Broadcasting (WebSockets)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-id
PUSHER_APP_KEY=your-key
PUSHER_APP_SECRET=your-secret
PUSHER_APP_CLUSTER=mt1
```

### 2. Configuración en config/services.php
```php
'showdown' => [
    'url' => env('SHOWDOWN_URL', 'http://localhost:9000'),
    'timeout' => env('SHOWDOWN_TIMEOUT', 30),
],
```

---

## 🚀 Verificar Que Todo Funciona

### 1. Verificar Showdown está corriendo
```bash
# En una terminal
cd pokemon-showdown-master
$env:PORT=9000
npm run start-microservice
```

Deberías ver:
```
Battle microservice running on port 9000
```

### 2. Probar desde Laravel
```bash
php artisan tinker
```

```php
$integration = app(\App\Services\ShowdownIntegration::class);

// Verificar si el servicio está disponible
$health = $integration->getHealth();
dd($health);
```

Resultado esperado:
```
array:1 [
  "status" => "ok"
]
```

### 3. Probar creación de batalla
```php
$client = new \App\Services\PokemonBattleClient('http://localhost:9000');

$battleId = $client->createBattle(
    'gen9customgame',
    'Pikachu|Assault Vest|Lightningrod||Thunderbolt,Volt Switch,Nuzzle,Play Nice|EVs: 252 SpA / 252 Spe / 4 HP|Timid|',
    'Player1',
    'Charizard|Charizardite X|Blaze||Flamethrower,Dragon Claw,Roost,Swords Dance|EVs: 252 SpA / 252 Spe / 4 HP|Timid|',
    'Player2'
);

echo "Battle created: $battleId";
```

---

## 🐛 Solucionar Problemas

### Problema: "Connection refused" 

**Causa:** El microservicio no está corriendo

**Solución:**
```bash
# Terminal 1
cd pokemon-showdown-master
$env:PORT=9000
npm run start-microservice
```

Verifica el puerto 9000:
```powershell
netstat -ano | findstr :9000
```

### Problema: "Failed to create battle"

**Causa:** Formato de equipo incorrecto o servidor no responde

**Solución:**
```php
// Usar el builder helper
$team = \App\Services\PokemonBattleClient::buildTeam([
    [
        'name' => 'Pikachu',
        'item' => 'Assault Vest',
        'ability' => 'Static',
        'moves' => ['Thunderbolt', 'Volt Switch'],
        'evs' => ['SpA' => 252, 'Spe' => 252],
        'nature' => 'Timid'
    ]
]);

$battleId = $client->createBattle('gen9customgame', $team, 'P1', $team, 'P2');
```

### Problema: "Health check failed"

**Causa:** Timeout en la conexión

**Solución:** Aumentar timeout en `.env`:
```env
SHOWDOWN_TIMEOUT=60
```

---

## 📡 Endpoints del Microservicio

El microservicio expone estos endpoints:

```
POST   /battle/create        - Crear batalla
POST   /battle/turn          - Enviar turno
GET    /battle/state/:id     - Ver estado
GET    /battle/logs/:id      - Ver logs
POST   /battle/finish        - Terminar batalla
DELETE /battle/:id           - Limpiar batalla
GET    /battles              - Listar activas
GET    /health               - Health check
GET    /api                  - Documentación API
```

### Ejemplos de Requests

#### Crear Batalla
```bash
POST http://localhost:9000/battle/create
Content-Type: application/json

{
  "formatId": "gen9customgame",
  "p1name": "Player1",
  "p1team": "Pikachu|Assault Vest|...",
  "p2name": "Player2",
  "p2team": "Charizard|Charizardite X|..."
}
```

#### Enviar Turno
```bash
POST http://localhost:9000/battle/turn
Content-Type: application/json

{
  "battleId": "battle-123",
  "p1Move": ">move 1",
  "p2Move": ">move 2"
}
```

#### Ver Estado
```bash
GET http://localhost:9000/battle/state/battle-123
```

---

## 🎯 Flujo de Una Batalla

```
1. Crear Batalla
   ↓
   ShowdownIntegration::createBattle($battle, $p1, $p2)
   ↓
   PokemonBattleClient::createBattle(...)
   ↓
   POST /battle/create → Retorna battleId
   ↓
   Guardar battleId en: $battle->showdown_id

2. Jugador Hace Movimiento
   ↓
   Determinar acción: '>move 1' o '>switch 2'
   ↓
   ShowdownIntegration::submitTurn(...)
   ↓
   PokemonBattleClient::submitTurn(...)
   ↓
   POST /battle/turn → Retorna resultado del turno
   ↓
   Guardar en: TurnDecision

3. Obtener Estado Actual
   ↓
   ShowdownIntegration::getBattleState($battle)
   ↓
   PokemonBattleClient::getBattleState(battleId)
   ↓
   GET /battle/state/:id → Retorna estado
   ↓
   Usar para actualizar UI en tiempo real

4. Terminar Batalla
   ↓
   ShowdownIntegration::finishBattle($battle, 'p1')
   ↓
   PokemonBattleClient::finishBattle(...)
   ↓
   POST /battle/finish → Retorna datos finales
   ↓
   Guardar en: $battle->status = 'finished'
   ↓
   Guardar en: $battle->winner_id = ...
```

---

## 📝 Ejemplo Completo en Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Services\ShowdownIntegration;

class BattleController extends Controller
{
    public function createBattle(ShowdownIntegration $integration)
    {
        // Crear batalla en BD
        $battle = Battle::create([
            'type' => 'pvp',
            'format' => 'singles',
            'status' => 'active'
        ]);

        // Obtener jugadores y equipos
        $p1 = $battle->getPlayerBySlot('p1');
        $p2 = $battle->getPlayerBySlot('p2');

        try {
            // Crear en microservicio
            $battleId = $integration->createBattle($battle, $p1, $p2);
            
            return response()->json([
                'success' => true,
                'battleId' => $battleId
            ]);
        } catch (\Exception $e) {
            $battle->delete();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitMove(Battle $battle, ShowdownIntegration $integration)
    {
        $move = request()->input('move'); // 'move 1'
        $p1Action = ShowdownIntegration::buildAction('move', $move);
        $p2Action = '>move 1'; // AI o jugador 2

        try {
            $result = $integration->submitTurn(
                $battle,
                $p1,
                $p2,
                $p1Action,
                $p2Action
            );

            return response()->json(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

---

## ✅ Checklist de Configuración

- [ ] Showdown microservice corriendo en puerto 9000
- [ ] `.env` configurado con `SHOWDOWN_URL=http://localhost:9000`
- [ ] `config/services.php` tiene sección 'showdown'
- [ ] `AppServiceProvider` registra `ShowdownIntegration`
- [ ] Tests pasan: `php artisan tinker` → `app(\App\Services\ShowdownIntegration::class)->getHealth()`
- [ ] Controllers usan `ShowdownIntegration` en lugar de `ShowdownClient`
- [ ] Database migrations ejecutadas: `php artisan migrate`
- [ ] Rutas API configuradas en `routes/web.php`

---

## 📚 Documentación Relacionada

- `pokemon-showdown-master/MICROSERVICE.md` - API completa del microservicio
- `pokemon-showdown-master/SETUP-MICROSERVICE.md` - Cómo instalar el microservicio
- `POKEMON_BATTLE_BACKEND.md` - Arquitectura general del backend
- `API_TESTING_GUIDE.md` - Ejemplos de testing de API

---

## 🎮 Próximos Pasos

1. **Verificar integración:**
   ```php
   php artisan tinker
   $i = app(\App\Services\ShowdownIntegration::class);
   $i->getHealth();
   ```

2. **Crear batalla de prueba:**
   ```php
   $team = \App\Models\Team::first();
   $battle = \App\Models\Battle::first();
   $p1 = $battle->getPlayerBySlot('p1');
   $p2 = $battle->getPlayerBySlot('p2');
   
   $id = $i->createBattle($battle, $p1, $p2);
   ```

3. **Enviar movimiento:**
   ```php
   $result = $i->submitTurn($battle, $p1, $p2, '>move 1', '>move 2');
   ```

¡Tu integración está lista! 🚀
