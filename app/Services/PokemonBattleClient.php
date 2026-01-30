<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Exception;

/**
 * Pokémon Showdown Battle Client
 *
 * Provides a Laravel-friendly interface to the Pokémon Showdown battle microservice.
 *
 * Usage:
 * $client = new PokemonBattleClient('http://localhost:9000');
 * $battleId = $client->createBattle($formatId, $p1Team, $p1Name, $p2Team, $p2Name);
 * $client->submitTurn($battleId, '>move 1', '>move 1');
 */
class PokemonBattleClient
{
    private string $baseUrl;
    private int $timeout = 30;

    public function __construct(string $baseUrl = 'http://localhost:9000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Check if the microservice is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get health status of the microservice
     */
    public function getHealth(): array
    {
        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/health");
            return $response->json() ?? ['status' => 'error'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get API documentation
     */
    public function getApiDocs(): array
    {
        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/api");
            return $response->json() ?? [];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a new battle
     *
     * @param string $formatId Format like 'gen9customgame', 'gen8customgame', etc.
     * @param string $p1Team Player 1 team in Showdown format
     * @param string $p1Name Player 1 name
     * @param string $p2Team Player 2 team in Showdown format
     * @param string $p2Name Player 2 name
     * @return string Battle ID
     * @throws Exception
     */
    public function createBattle(
        string $formatId,
        string $p1Team,
        string $p1Name,
        string $p2Team,
        string $p2Name
    ): string {
        try {
            $response = Http::timeout($this->timeout)->post("{$this->baseUrl}/battle/create", [
                'formatId' => $formatId,
                'p1name' => $p1Name,
                'p1team' => $p1Team,
                'p2name' => $p2Name,
                'p2team' => $p2Team,
            ]);

            if (!$response->successful()) {
                throw new Exception("Failed to create battle: " . $response->body());
            }

            $data = $response->json();
            if (!isset($data['battleId'])) {
                throw new Exception("No battleId in response");
            }

            return $data['battleId'];
        } catch (Exception $e) {
            throw new Exception("Error creating battle: " . $e->getMessage());
        }
    }

    /**
     * Submit a turn in a battle
     *
     * @param string $battleId Battle ID
     * @param string $p1Move Player 1 move (e.g., '>move 1', '>switch 2')
     * @param string $p2Move Player 2 move
     * @return array Turn response
     * @throws Exception
     */
    public function submitTurn(string $battleId, string $p1Move, string $p2Move): array
    {
        try {
            $response = Http::timeout($this->timeout)->post("{$this->baseUrl}/battle/turn", [
                'battleId' => $battleId,
                'p1Move' => $p1Move,
                'p2Move' => $p2Move,
            ]);

            if (!$response->successful()) {
                throw new Exception("Failed to submit turn: " . $response->body());
            }

            return $response->json() ?? [];
        } catch (Exception $e) {
            throw new Exception("Error submitting turn: " . $e->getMessage());
        }
    }

    /**
     * Get the current state of a battle
     *
     * @param string $battleId Battle ID
     * @return array Battle state
     * @throws Exception
     */
    public function getBattleState(string $battleId): array
    {
        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/battle/state/{$battleId}");

            if (!$response->successful()) {
                throw new Exception("Battle not found or error occurred");
            }

            return $response->json() ?? [];
        } catch (Exception $e) {
            throw new Exception("Error getting battle state: " . $e->getMessage());
        }
    }

    /**
     * Get all logs for a battle
     *
     * @param string $battleId Battle ID
     * @return array Battle logs
     * @throws Exception
     */
    public function getBattleLogs(string $battleId): array
    {
        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/battle/logs/{$battleId}");

            if (!$response->successful()) {
                throw new Exception("Battle not found");
            }

            return $response->json() ?? [];
        } catch (Exception $e) {
            throw new Exception("Error getting battle logs: " . $e->getMessage());
        }
    }

    /**
     * Finish a battle
     *
     * @param string $battleId Battle ID
     * @param string|null $winner 'p1' or 'p2' (optional)
     * @return array Final battle data
     * @throws Exception
     */
    public function finishBattle(string $battleId, ?string $winner = null): array
    {
        try {
            $payload = ['battleId' => $battleId];
            if ($winner) {
                $payload['winner'] = $winner;
            }

            $response = Http::timeout($this->timeout)->post("{$this->baseUrl}/battle/finish", $payload);

            if (!$response->successful()) {
                throw new Exception("Failed to finish battle");
            }

            return $response->json() ?? [];
        } catch (Exception $e) {
            throw new Exception("Error finishing battle: " . $e->getMessage());
        }
    }

    /**
     * Delete a battle (cleanup)
     *
     * @param string $battleId Battle ID
     * @return bool Success
     * @throws Exception
     */
    public function deleteBattle(string $battleId): bool
    {
        try {
            $response = Http::timeout($this->timeout)->delete("{$this->baseUrl}/battle/{$battleId}");
            return $response->successful();
        } catch (Exception $e) {
            throw new Exception("Error deleting battle: " . $e->getMessage());
        }
    }

    /**
     * List all active battles
     *
     * @return array Active battles
     * @throws Exception
     */
    public function listBattles(): array
    {
        try {
            $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/battles");

            if (!$response->successful()) {
                throw new Exception("Failed to list battles");
            }

            return $response->json() ?? [];
        } catch (Exception $e) {
            throw new Exception("Error listing battles: " . $e->getMessage());
        }
    }

    /**
     * Helper: Parse battle logs to extract useful information
     *
     * @param array $logs Raw logs from the API
     * @return array Parsed information
     */
    public static function parseLogs(array $logs): array
    {
        $parsed = [
            'turns' => 0,
            'p1pokemon' => [],
            'p2pokemon' => [],
            'events' => [],
            'winner' => null,
        ];

        foreach ($logs as $log) {
            if (strpos($log, '|turn|') === 0) {
                $parsed['turns']++;
            }
            if (strpos($log, '|win|') === 0) {
                $parsed['winner'] = str_replace('|win|', '', $log);
            }
            if (strpos($log, '|faint|') === 0) {
                $parsed['events'][] = ['type' => 'faint', 'data' => $log];
            }
            if (strpos($log, '|move|') === 0) {
                $parsed['events'][] = ['type' => 'move', 'data' => $log];
            }
        }

        return $parsed;
    }

    /**
     * Helper: Build a team string in Showdown format
     *
     * Example:
     * [
     *   ['name' => 'Pikachu', 'item' => 'Assault Vest', 'ability' => 'Lightning Rod',
     *    'moves' => ['Thunderbolt', 'Volt Switch'], 'evs' => ['SpA' => 252, 'Spe' => 252]]
     * ]
     */
    public static function buildTeam(array $pokemons): string
    {
        $teamLines = [];

        foreach ($pokemons as $pokemon) {
            $line = $pokemon['name'] ?? 'Unknown';
            $line .= '|' . ($pokemon['item'] ?? '');
            $line .= '|' . ($pokemon['ability'] ?? '');
            $line .= '|' . ($pokemon['gender'] ?? '');

            $moves = implode(',', $pokemon['moves'] ?? []);
            $line .= '|' . $moves;

            $evString = '';
            if (isset($pokemon['evs'])) {
                $evParts = [];
                foreach ($pokemon['evs'] as $stat => $value) {
                    $evParts[] = "$value $stat";
                }
                $evString = 'EVs: ' . implode(' / ', $evParts);
            }
            $line .= '|' . $evString;

            $line .= '|' . ($pokemon['nature'] ?? 'Timid');
            $line .= '|';

            $teamLines[] = $line;
        }

        return implode("\n", $teamLines);
    }
}
