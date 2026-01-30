<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BattleShowdownController extends Controller
{
    private string $battleServerUrl = 'http://localhost:9000';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar la página principal de batallas
     */
    public function index(): View
    {
        $battleServerAvailable = $this->isBattleServerAvailable();

        return view('battles.showdown-index', [
            'battleServerAvailable' => $battleServerAvailable,
            'userPokemon' => auth()->user()->favorites()->pluck('pokemon_id')->toArray()
        ]);
    }

    /**
     * Mostrar formulario para crear una batalla
     */
    public function create(): View
    {
        return view('battles.showdown-create', [
            'userFavorites' => auth()->user()->favorites()->get()
        ]);
    }

    /**
     * Crear una nueva batalla
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'p1name' => 'required|string|max:20',
                'p1team' => 'nullable|string',
                'p2name' => 'required|string|max:20',
                'p2team' => 'nullable|string',
                'formatId' => 'required|string',
                'mode' => 'required|in:custom,random'
            ]);

            $formatId = $validated['formatId'];
            $p1team = $validated['p1team'] ?? '';
            $p2team = $validated['p2team'] ?? '';
            $mode = $validated['mode'];

            // Si es modo random, generar equipos aleatorios
            if ($mode === 'random') {
                $p1team = $this->generateRandomTeam();
                $p2team = $this->generateRandomTeam();
            }

            if (empty($p1team) || empty($p2team)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Equipo inválido o vacío. Por favor, ingresa equipos válidos.'
                ], 400);
            }

            // Llamar al microservicio de batallas
            $response = Http::timeout(5)->post("{$this->battleServerUrl}/battle/create", [
                'formatId' => $formatId,
                'p1name' => $validated['p1name'],
                'p1team' => $p1team,
                'p2name' => $validated['p2name'],
                'p2team' => $p2team
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'battleId' => $data['battleId'] ?? null,
                    'message' => 'Batalla creada exitosamente'
                ]);
            }

            $errorData = $response->json();
            return response()->json([
                'success' => false,
                'message' => $errorData['error'] ?? 'Error al crear la batalla en el servidor'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . collect($e->errors())->flatten()->first()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear batalla: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la batalla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una batalla en progreso
     */
    public function show(string $battleId): View
    {
        try {
            $response = Http::get("{$this->battleServerUrl}/battle/state/{$battleId}");

            if (!$response->successful()) {
                abort(404, 'Batalla no encontrada');
            }

            $battle = $response->json();

            return view('battles.showdown-show', [
                'battleId' => $battleId,
                'battle' => $battle
            ]);

        } catch (\Exception $e) {
            abort(500, 'Error al obtener la batalla: ' . $e->getMessage());
        }
    }

    /**
     * Enviar un movimiento en la batalla (AJAX)
     */
    public function submitMove(Request $request, string $battleId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'p1Move' => 'required|string',
                'p2Move' => 'required|string'
            ]);

            $response = Http::post("{$this->battleServerUrl}/battle/turn", [
                'battleId' => $battleId,
                'p1Move' => $validated['p1Move'],
                'p2Move' => $validated['p2Move']
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'turn' => $response['turn'],
                    'logs' => $response['logs']
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $response['error'] ?? 'Error al procesar el movimiento'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener los logs de una batalla
     */
    public function getLogs(string $battleId): JsonResponse
    {
        try {
            $response = Http::get("{$this->battleServerUrl}/battle/logs/{$battleId}");

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'logs' => $response['logs'] ?? [],
                    'turn' => $response['turn'] ?? 0
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Batalla no encontrada'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar una batalla
     */
    public function finish(Request $request, string $battleId): JsonResponse
    {
        try {
            $response = Http::post("{$this->battleServerUrl}/battle/finish", [
                'battleId' => $battleId
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Batalla finalizada'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Error al finalizar la batalla'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas las batallas activas
     */
    public function listBattles(): JsonResponse
    {
        try {
            $response = Http::get("{$this->battleServerUrl}/battles");

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'battles' => $response['battles'] ?? [],
                    'total' => $response['total'] ?? 0
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Error al obtener las batallas'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si el servidor de batallas está disponible
     */
    private function isBattleServerAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get("{$this->battleServerUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generar equipo aleatorio de Pokémon nivel 100
     */
    private function generateRandomTeam(): string
    {
        $pokemons = [
            // Gen 1
            'Pikachu', 'Charizard', 'Blastoise', 'Venusaur', 'Arcanine', 'Gyarados', 'Lapras', 'Dragonite', 'Alakazam', 'Gengar',
            // Gen 2
            'Typhlosion', 'Feraligatr', 'Meganium', 'Ampharos', 'Togetic', 'Azumarill', 'Houndoom', 'Donphan', 'Porygon2', 'Tyranitar',
            // Gen 3
            'Blaziken', 'Swampert', 'Sceptile', 'Manectric', 'Crobat', 'Salamence', 'Metagross', 'Gardevoir', 'Aggron', 'Milotic',
            // Gen 4
            'Infernape', 'Empoleon', 'Torterra', 'Luxray', 'Garchomp', 'Crobat', 'Roserade', 'Weavile', 'Gliscor', 'Togekiss',
            // Gen 5
            'Emboar', 'Samurott', 'Serperior', 'Eelektross', 'Haxorus', 'Excadrill', 'Krookodile', 'Scrafty', 'Volcarona', 'Hydreigon',
            // Gen 6
            'Chesnaught', 'Delphox', 'Greninja', 'Ampharos', 'Tyrantrum', 'Aurorus', 'Goodra', 'Talonflame', 'Kangaskhan', 'Gengar',
            // Gen 7
            'Decidueye', 'Incineroar', 'Primarina', 'Togedemaru', 'Kommo-o', 'Mimikyu', 'Bewear', 'Salazzle', 'Turtonator', 'Vikavolt',
            // Gen 8
            'Cinderace', 'Inteleon', 'Rillaboom', 'Toxtricity', 'Corviknight', 'Grimmsnarl', 'Dragapult', 'Hydreigon', 'Excadrill', 'Rotom-Wash',
            // Gen 9
            'Blaziken', 'Samurott', 'Meowscarada', 'Arcanine', 'Clodsire', 'Ampharos', 'Scizor', 'Dragonite', 'Alakazam', 'Gengar'
        ];

        $abilities = [
            'Blaze', 'Torrent', 'Overgrow', 'Static', 'Lightning Rod', 'Water Absorb',
            'Sand Stream', 'Drought', 'Rain Dish', 'Solar Power', 'Adaptability',
            'Multiscale', 'Regenerator', 'Compound Eyes', 'Wonder Guard', 'Download'
        ];

        $items = [
            'Life Orb', 'Choice Specs', 'Choice Scarf', 'Choice Band', 'Assault Vest',
            'Leftovers', 'Rocky Helmet', 'Air Balloon', 'Expert Belt', 'Weakness Policy',
            'Charcoal', 'Flame Orb', 'Trick Room', 'Eviolite'
        ];

        $genders = ['M', 'F'];
        $natures = [
            'Adamant', 'Modest', 'Timid', 'Jolly', 'Calm', 'Bold', 'Careful', 'Quiet',
            'Gentle', 'Sassy', 'Lonely', 'Mild', 'Rash', 'Lax', 'Hasty', 'Naive'
        ];

        $moves = [
            'Thunderbolt', 'Fire Blast', 'Ice Beam', 'Hydro Pump', 'Earthquake',
            'Stone Edge', 'Close Combat', 'Aura Sphere', 'Flash Cannon', 'Power Gem',
            'Energy Ball', 'Focus Blast', 'Trick Room', 'Stealth Rock', 'Spikes',
            'Dragon Dance', 'Sword Dance', 'Nasty Plot', 'Bulk Up', 'Dragon Claw',
            'Iron Head', 'Play Rough', 'Moonblast', 'Dazzling Gleam', 'Shadow Ball'
        ];

        $team = [];
        $selectedPokes = array_rand(array_flip($pokemons), 6);

        foreach ($selectedPokes as $pokeName) {
            $ability = $abilities[array_rand($abilities)];
            $item = $items[array_rand($items)];
            $gender = $genders[array_rand($genders)];
            $nature = $natures[array_rand($natures)];

            // Seleccionar 4 movimientos aleatorios
            $pokeMoves = array_rand(array_flip($moves), 4);
            $movesStr = implode(',', $pokeMoves);

            // Formato: Name|Item|Ability|Gender|Moves|EVs|Nature
            $pokemonStr = "{$pokeName}|{$item}|{$ability}|{$gender}|{$movesStr}|EVs: 252 Spd 252 SpA 4 HP|{$nature}";
            $team[] = $pokemonStr;
        }

        return implode("\n", $team);
    }
}
