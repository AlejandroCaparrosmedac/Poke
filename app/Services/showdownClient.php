<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Models\Battle;
use App\Models\BattlePlayer;

class ShowdownClient
{
    private string $baseUrl;
    private int $timeout = 30;

    public function __construct()
    {
        $this->baseUrl = config('services.showdown.url', 'http://localhost:9000');
    }

    /**
     * Create a new battle on the Showdown microservice.
     */
    public function createBattle(Battle $battle, BattlePlayer $p1, BattlePlayer $p2): array
    {
        $p1Team = $p1->team->getPokemons();
        $p2Team = $p2->team->getPokemons();

        $response = $this->post('/api/battle/create', [
            'format' => $battle->format,
            'type' => $battle->type,
            'p1Team' => $p1Team,
            'p2Team' => $p2Team,
            'p1Name' => $p1->user->name,
            'p2Name' => $p2->user->name,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            // Store Showdown IDs
            $battle->showdown_id = $data['battleId'] ?? null;
            $battle->showdown_room_id = $data['roomId'] ?? null;
            $battle->save();

            return $data;
        }

        throw new \Exception('Failed to create battle on Showdown: ' . $response->body());
    }

    /**
     * Submit a move to Showdown.
     */
    public function submitMove(Battle $battle, string $playerSlot, string $moveName): array
    {
        $response = $this->post('/api/battle/move', [
            'battleId' => $battle->showdown_id,
            'playerSlot' => $playerSlot,
            'move' => $moveName,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Failed to submit move: {$response->body()}");
    }

    /**
     * Switch a Pokémon.
     */
    public function switchPokemon(Battle $battle, string $playerSlot, int $pokemonIndex): array
    {
        $response = $this->post('/api/battle/switch', [
            'battleId' => $battle->showdown_id,
            'playerSlot' => $playerSlot,
            'pokemonIndex' => $pokemonIndex,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Failed to switch Pokémon: {$response->body()}");
    }

    /**
     * Get the current state of a battle.
     */
    public function getBattleState(Battle $battle): array
    {
        $response = $this->get("/api/battle/{$battle->showdown_id}/state");

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Failed to get battle state: {$response->body()}");
    }

    /**
     * Forfeit a battle.
     */
    public function forfeitBattle(Battle $battle, string $playerSlot): array
    {
        $response = $this->post('/api/battle/forfeit', [
            'battleId' => $battle->showdown_id,
            'playerSlot' => $playerSlot,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Failed to forfeit battle: {$response->body()}");
    }

    /**
     * Get available moves for a Pokémon.
     */
    public function getAvailableMoves(string $pokemonName, int $level = 50): array
    {
        $response = $this->get("/api/pokemon/{$pokemonName}/moves", [
            'level' => $level,
        ]);

        if ($response->successful()) {
            return $response->json()['moves'] ?? [];
        }

        return [];
    }

    /**
     * Get Pokémon stats.
     */
    public function getPokemonStats(string $pokemonName): array
    {
        $response = $this->get("/api/pokemon/{$pokemonName}/stats");

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Send HTTP POST request.
     */
    private function post(string $endpoint, array $data = []): Response
    {
        return Http::timeout($this->timeout)
            ->post("{$this->baseUrl}{$endpoint}", $data);
    }

    /**
     * Send HTTP GET request.
     */
    private function get(string $endpoint, array $query = []): Response
    {
        return Http::timeout($this->timeout)
            ->get("{$this->baseUrl}{$endpoint}", $query);
    }

    /**
     * Set a custom base URL (useful for testing).
     */
    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }
}
