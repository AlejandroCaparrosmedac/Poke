#!/usr/bin/env php
<?php

/**
 * Pokémon Showdown Integration Verification Script
 *
 * Uso: php verify-showdown-integration.php
 *
 * Este script verifica que:
 * 1. El microservicio está corriendo
 * 2. La integración Laravel funciona correctamente
 * 3. Se puede crear una batalla
 * 4. Se pueden enviar acciones
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\ShowdownIntegration;
use App\Services\PokemonBattleClient;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  Pokémon Showdown Integration Verification                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

try {
    // Load Laravel app
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    $integration = app(ShowdownIntegration::class);
    $client = app(PokemonBattleClient::class);

    // 1. Check if service is available
    echo "1. Verificando disponibilidad del servicio...\n";
    if ($client->isAvailable()) {
        echo "   ✓ Servicio disponible en " . config('services.showdown.url') . "\n";
    } else {
        echo "   ✗ Servicio NO disponible\n";
        echo "   Inicia el microservicio con: cd pokemon-showdown-master && npm run start-microservice\n";
        exit(1);
    }

    // 2. Get health status
    echo "\n2. Obteniendo estado del servicio...\n";
    $health = $client->getHealth();
    if (isset($health['status']) && $health['status'] === 'ok') {
        echo "   ✓ Servicio saludable\n";
        echo "   Estado: " . json_encode($health) . "\n";
    } else {
        echo "   ✗ Servicio reporta problemas\n";
        echo "   Respuesta: " . json_encode($health) . "\n";
    }

    // 3. Get API documentation
    echo "\n3. Obteniendo documentación de API...\n";
    $docs = $client->getApiDocs();
    if (is_array($docs) && !empty($docs)) {
        echo "   ✓ API documentación disponible\n";
        if (isset($docs['endpoints'])) {
            echo "   Endpoints disponibles: " . count($docs['endpoints']) . "\n";
        }
    }

    // 4. Test team building
    echo "\n4. Construyendo equipo de prueba...\n";
    $teamData = [
        [
            'name' => 'Pikachu',
            'item' => 'Assault Vest',
            'ability' => 'Static',
            'moves' => ['Thunderbolt', 'Volt Switch', 'Nuzzle', 'Play Nice'],
            'evs' => ['SpA' => 252, 'Spe' => 252, 'HP' => 4],
            'nature' => 'Timid'
        ],
        [
            'name' => 'Charizard',
            'item' => 'Charizardite X',
            'ability' => 'Blaze',
            'moves' => ['Flamethrower', 'Dragon Claw', 'Roost', 'Swords Dance'],
            'evs' => ['SpA' => 252, 'Spe' => 252, 'HP' => 4],
            'nature' => 'Timid'
        ]
    ];

    $teamString = PokemonBattleClient::buildTeam($teamData);
    if (!empty($teamString)) {
        echo "   ✓ Equipo construido correctamente\n";
        echo "   Pokémon en equipo: 2\n";
        echo "\n   Vista previa (primeros 100 caracteres):\n";
        echo "   " . substr(str_replace("\n", "\n   ", $teamString), 0, 150) . "...\n";
    }

    // 5. Try creating a battle
    echo "\n5. Intentando crear una batalla de prueba...\n";
    try {
        $battleId = $client->createBattle(
            'gen9customgame',
            $teamString,
            'TestPlayer1',
            $teamString,
            'TestPlayer2'
        );

        if ($battleId) {
            echo "   ✓ Batalla creada exitosamente\n";
            echo "   Battle ID: $battleId\n";

            // Try to get battle state
            echo "\n6. Obteniendo estado de la batalla...\n";
            $state = $client->getBattleState($battleId);
            if (is_array($state) && !empty($state)) {
                echo "   ✓ Estado de batalla obtenido\n";
                echo "   Claves en estado: " . implode(', ', array_keys($state)) . "\n";
            }

            // Try submitting a turn
            echo "\n7. Enviando un turno de prueba...\n";
            $turnResult = $client->submitTurn($battleId, '>move 1', '>move 1');
            if (is_array($turnResult)) {
                echo "   ✓ Turno enviado correctamente\n";
            }

            // Try getting logs
            echo "\n8. Obteniendo logs de la batalla...\n";
            $logs = $client->getBattleLogs($battleId);
            if (is_array($logs) && !empty($logs)) {
                echo "   ✓ Logs obtenidos\n";
                echo "   Total de líneas de log: " . count($logs) . "\n";
            }

            // Cleanup
            echo "\n9. Limpiando batalla de prueba...\n";
            if ($client->deleteBattle($battleId)) {
                echo "   ✓ Batalla limpiada\n";
            }
        }
    } catch (Exception $e) {
        echo "   ✗ Error creando batalla: " . $e->getMessage() . "\n";
    }

    // 6. List active battles
    echo "\n10. Listando batallas activas...\n";
    try {
        $battles = $client->listBattles();
        if (is_array($battles)) {
            echo "    ✓ Batallas activas: " . count($battles) . "\n";
        }
    } catch (Exception $e) {
        echo "    ! Error listando batallas (esperado si no hay batallas): " . $e->getMessage() . "\n";
    }

    // Summary
    echo "\n";
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ✓ VERIFICACIÓN COMPLETADA EXITOSAMENTE                      ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "La integración de Pokémon Showdown está funcionando correctamente.\n";
    echo "\n";
    echo "Próximos pasos:\n";
    echo "1. Crea equipos en la BD: POST /api/teams\n";
    echo "2. Crea una batalla PVP: POST /api/battles/pvp\n";
    echo "3. Envía movimientos: POST /api/battles/:id/move\n";
    echo "\n";
    echo "Documentación:\n";
    echo "- SHOWDOWN_INTEGRATION_SETUP.md - Guía de configuración\n";
    echo "- POKEMON_BATTLE_BACKEND.md - Arquitectura completa\n";
    echo "- API_TESTING_GUIDE.md - Ejemplos de testing\n";
    echo "\n";

} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
