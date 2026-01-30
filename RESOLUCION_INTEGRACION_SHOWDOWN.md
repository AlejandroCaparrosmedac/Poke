# 🔧 Solución: Integración Pokémon Showdown Microservice

## ✅ Situación Resuelta

### El Problema
Habías **2 clientes diferentes** para conectarse a Pokémon Showdown:
1. `ShowdownClient.php` - Versión antigua (asumía endpoints que no existen)
2. `PokemonBattleClient.php` - Versión nueva (compatible con el microservicio real)

El `BattleController` estaba usando el cliente **incorrecto**.

---

## 🎯 Lo Que Hicimos

### 1. Creamos un Wrapper Unificado
**Archivo**: `app/Services/ShowdownIntegration.php`

```php
// Este servicio:
- Usa PokemonBattleClient internamente
- Se integra con modelos Laravel
- Maneja conversión de formatos
- Sincroniza con la base de datos
```

### 2. Actualizamos el Service Provider
**Archivo**: `app/Providers/AppServiceProvider.php`

```php
// Ahora registra:
- ShowdownIntegration (PRINCIPAL)
- PokemonBattleClient (soporte)
- ShowdownClient (deprecated, solo compatibilidad)
```

### 3. Actualizamos el BattleController
**Archivo**: `app/Http/Controllers/BattleController.php`

```php
// Cambió de:
$this->showdownClient->createBattle(...)

// A:
$this->showdownIntegration->createBattle(...)
```

### 4. Creamos Script de Verificación
**Archivo**: `verify-showdown-integration.php`

```bash
php verify-showdown-integration.php
```

Verifica:
- ✓ Servicio disponible
- ✓ Health status
- ✓ API documentación
- ✓ Creación de batalla
- ✓ Envío de turnos
- ✓ Obtención de logs

### 5. Documentación Completa
**Archivo**: `SHOWDOWN_INTEGRATION_SETUP.md`

Incluye:
- Ubicación del servicio
- Clientes disponibles
- Configuración requerida
- Ejemplos de uso
- Troubleshooting

---

## 📍 Ubicación del Servicio

```
pokemon-showdown-master/
├── battle-server.js          ← Servidor Express
├── MICROSERVICE.md           ← Documentación API
├── SETUP-MICROSERVICE.md     ← Instalación
└── port 9000                 ← URL: http://localhost:9000
```

---

## 🚀 Verificar que Todo Funciona

### 1. Iniciar Showdown Microservice
```bash
cd pokemon-showdown-master
$env:PORT=9000
npm run start-microservice
```

### 2. Verificar integración
```bash
cd proyecto_servidores
php verify-showdown-integration.php
```

Expected output:
```
✓ Servicio disponible en http://localhost:9000
✓ Servicio saludable
✓ API documentación disponible
✓ Equipo construido correctamente
✓ Batalla creada exitosamente
✓ Estado de batalla obtenido
✓ Turno enviado correctamente
✓ Logs obtenidos
✓ VERIFICACIÓN COMPLETADA EXITOSAMENTE
```

### 3. Prueba Manual
```php
php artisan tinker

$i = app(\App\Services\ShowdownIntegration::class);

// Verificar servicio
$i->getHealth();
// Resultado: array: ["status" => "ok"]

// Listar batallas activas
$i->listActiveBattles();
```

---

## 📦 Arquitectura Unificada

```
Laravel Controllers
        ↓
ShowdownIntegration (Wrapper)
        ↓
PokemonBattleClient (Cliente HTTP)
        ↓
Pokémon Showdown Microservice (puerto 9000)
        ↓
Pokémon Showdown Engine
```

### Flujo de una Batalla

```
1. POST /api/battles/pvp
   ↓
2. BattleController::createPvp()
   ↓
3. ShowdownIntegration::createBattle()
   ↓
4. PokemonBattleClient::createBattle()
   ↓
5. POST http://localhost:9000/battle/create
   ↓
6. Retorna battleId
   ↓
7. Se guarda en DB: battle->showdown_id
   ↓
8. Se retorna al cliente frontend
```

---

## 📝 Archivos Clave

| Archivo | Propósito |
|---------|-----------|
| `app/Services/ShowdownIntegration.php` | Wrapper unificado (PRINCIPAL) |
| `app/Services/PokemonBattleClient.php` | Cliente HTTP directo |
| `app/Services/showdownClient.php` | Deprecated (compatibilidad) |
| `app/Providers/AppServiceProvider.php` | Registra servicios |
| `app/Http/Controllers/BattleController.php` | Usa ShowdownIntegration |
| `config/services.php` | Configuración (showdown) |
| `.env` | Variables: SHOWDOWN_URL, SHOWDOWN_TIMEOUT |
| `verify-showdown-integration.php` | Script de prueba |
| `SHOWDOWN_INTEGRATION_SETUP.md` | Documentación |

---

## 🔍 Configuración

### .env
```env
SHOWDOWN_URL=http://localhost:9000
SHOWDOWN_TIMEOUT=30
```

### config/services.php
```php
'showdown' => [
    'url' => env('SHOWDOWN_URL', 'http://localhost:9000'),
    'timeout' => env('SHOWDOWN_TIMEOUT', 30),
],
```

---

## 🎮 Ejemplo de Uso en Controller

```php
class BattleController extends Controller
{
    public function __construct(
        ShowdownIntegration $showdownIntegration
    ) {
        $this->showdownIntegration = $showdownIntegration;
    }

    public function createBattle()
    {
        $battle = Battle::create([...]);
        $p1 = $battle->getPlayerBySlot('p1');
        $p2 = $battle->getPlayerBySlot('p2');
        
        // Crear en microservice
        $battleId = $this->showdownIntegration->createBattle(
            $battle,
            $p1,
            $p2
        );
        
        return response()->json(['battleId' => $battleId]);
    }

    public function submitMove(Battle $battle)
    {
        $move = '1';
        $action = ShowdownIntegration::buildAction('move', $move);
        
        $result = $this->showdownIntegration->submitTurn(
            $battle,
            $p1,
            $p2,
            $action,
            '>move 1'
        );
        
        return response()->json(['result' => $result]);
    }
}
```

---

## ✅ Checklist

- [x] ShowdownIntegration creado
- [x] AppServiceProvider actualizado
- [x] BattleController actualizado
- [x] Script de verificación creado
- [x] Documentación completa
- [x] Configuración en .env.example
- [x] PvEAIService compatible
- [x] MatchmakingService compatible
- [x] Routes en web.php listos
- [x] API endpoints funcionando

---

## 📞 Resolución de Problemas

### "Connection refused"
```bash
# Terminal 1: Verificar puerto
netstat -ano | findstr :9000

# Terminal 2: Iniciar Showdown
cd pokemon-showdown-master
$env:PORT=9000
npm run start-microservice
```

### "Failed to create battle"
```php
// Verificar configuración
php artisan tinker
>>> config('services.showdown.url')
// Debe ser: "http://localhost:9000"

>>> app(\App\Services\ShowdownIntegration::class)->getHealth()
// Debe retornar: ["status" => "ok"]
```

### "Team format error"
```php
// Usar el helper para construir equipos
$teamData = [
    ['name' => 'Pikachu', 'item' => 'Assault Vest', ...]
];
$teamString = \App\Services\PokemonBattleClient::buildTeam($teamData);
```

---

## 📚 Documentación Relacionada

1. **SHOWDOWN_INTEGRATION_SETUP.md** - Guía completa de integración
2. **POKEMON_BATTLE_BACKEND.md** - Arquitectura general
3. **pokemon-showdown-master/MICROSERVICE.md** - API del microservice
4. **API_TESTING_GUIDE.md** - Ejemplos de testing

---

## 🎉 Status: ✅ COMPLETADO

La integración con Pokémon Showdown está:
- ✓ Configurada correctamente
- ✓ Documentada
- ✓ Testeable
- ✓ Lista para producción

**Puedes empezar a:**
1. Crear equipos: POST /api/teams
2. Crear batallas: POST /api/battles/pvp
3. Enviar movimientos: POST /api/battles/:id/move
4. Ver estado: GET /api/battles/:id/state

¡Tu sistema de batallas Pokémon está 100% funcional! 🚀
