<?php

namespace App\Services;

use App\Models\Battle;
use App\Models\BattlePlayer;
use App\Models\Team;
use Exception;

/**
 * Showdown Integration Service
 *
 * Wrapper around PokemonBattleClient that integrates with Laravel models
 * and the battle system.
 */
class ShowdownIntegration
{
    private PokemonBattleClient $client;

    public function __construct()
    {
        $this->client = new PokemonBattleClient(
            config('services.showdown.url', 'http://localhost:9000')
        );
    }

    /**
     * Check if Showdown service is available
     */
    public function isAvailable(): bool
    {
        return $this->client->isAvailable();
    }

    /**
     * Get service health status
     */
    public function getHealth(): array
    {
        return $this->client->getHealth();
    }

    /**
     * Create a battle on Showdown and link to database
     */
    public function createBattle(
        Battle $battle,
        BattlePlayer $p1,
        BattlePlayer $p2,
        string $format = 'gen9customgame'
    ): string {
        try {
            // Build teams in Showdown format
            $p1Team = $this->buildTeamString($p1->team);
            $p2Team = $this->buildTeamString($p2->team);

            // Create battle on microservice
            $battleId = $this->client->createBattle(
                $format,
                $p1Team,
                $p1->user->name,
                $p2Team,
                $p2->user->name
            );

            // Store the Showdown battle ID
            $battle->showdown_id = $battleId;
            $battle->save();

            return $battleId;
        } catch (Exception $e) {
            throw new Exception("Failed to create battle on Showdown: " . $e->getMessage());
        }
    }

    /**
     * Submit a move/action in a battle
     */
    public function submitTurn(
        Battle $battle,
        BattlePlayer $p1Player,
        BattlePlayer $p2Player,
        string $p1Action,
        string $p2Action
    ): array {
        try {
            if (!$battle->showdown_id) {
                throw new Exception("Battle not initialized on Showdown");
            }

            $result = $this->client->submitTurn(
                $battle->showdown_id,
                $p1Action,
                $p2Action
            );

            return $result;
        } catch (Exception $e) {
            throw new Exception("Failed to submit turn: " . $e->getMessage());
        }
    }

    /**
     * Get current battle state
     */
    public function getBattleState(Battle $battle): array
    {
        try {
            if (!$battle->showdown_id) {
                throw new Exception("Battle not initialized on Showdown");
            }

            return $this->client->getBattleState($battle->showdown_id);
        } catch (Exception $e) {
            throw new Exception("Failed to get battle state: " . $e->getMessage());
        }
    }

    /**
     * Get battle logs
     */
    public function getBattleLogs(Battle $battle): array
    {
        try {
            if (!$battle->showdown_id) {
                throw new Exception("Battle not initialized on Showdown");
            }

            return $this->client->getBattleLogs($battle->showdown_id);
        } catch (Exception $e) {
            throw new Exception("Failed to get battle logs: " . $e->getMessage());
        }
    }

    /**
     * Finish a battle
     */
    public function finishBattle(Battle $battle, ?string $winner = null): array
    {
        try {
            if (!$battle->showdown_id) {
                throw new Exception("Battle not initialized on Showdown");
            }

            $result = $this->client->finishBattle($battle->showdown_id, $winner);

            // Update battle status in database
            $battle->status = 'finished';
            if ($winner === 'p1') {
                $battle->winner_id = $battle->getPlayerBySlot('p1')->user_id;
            } elseif ($winner === 'p2') {
                $battle->winner_id = $battle->getPlayerBySlot('p2')->user_id;
            }
            $battle->save();

            return $result;
        } catch (Exception $e) {
            throw new Exception("Failed to finish battle: " . $e->getMessage());
        }
    }

    /**
     * Delete/cleanup a battle
     */
    public function deleteBattle(Battle $battle): bool
    {
        try {
            if (!$battle->showdown_id) {
                return true;
            }

            return $this->client->deleteBattle($battle->showdown_id);
        } catch (Exception $e) {
            \Log::warning("Failed to delete battle {$battle->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build a team string in Showdown format from a Laravel Team model
     */
    private function buildTeamString(?Team $team): string
    {
        if (!$team) {
            // Return default team if none provided
            return $this->getDefaultTeam();
        }

        $pokemons = $team->getPokemons();

        if (empty($pokemons)) {
            return $this->getDefaultTeam();
        }

        $teamLines = [];
        foreach ($pokemons as $pokemon) {
            $line = $pokemon['name'] ?? 'Pikachu';

            // Add item
            $line .= '|' . ($pokemon['item'] ?? 'Leftovers');

            // Add ability
            $line .= '|' . ($pokemon['ability'] ?? '');

            // Add gender
            $line .= '|' . ($pokemon['gender'] ?? '');

            // Add moves
            $moves = implode(',', $pokemon['moves'] ?? ['Tackle']);
            $line .= '|' . $moves;

            // Add EVs
            $line .= '|' . 'EVs: 252 SpA / 252 Spe / 4 HP';

            // Add nature
            $line .= '|' . ($pokemon['nature'] ?? 'Timid');

            $teamLines[] = $line;
        }

        return implode("\n", $teamLines);
    }

    /**
     * Get a default team for testing
     */
    private function getDefaultTeam(): string
    {
        return <<<TEAM
Pikachu|Assault Vest|Lightningrod||Thunderbolt,Volt Switch,Nuzzle,Play Nice|EVs: 252 SpA / 252 Spe / 4 HP|Timid|
Charizard|Charizardite X|Blaze||Flamethrower,Dragon Claw,Roost,Swords Dance|EVs: 252 SpA / 252 Spe / 4 HP|Timid|
Blastoise|Assault Vest|Torrent||Hydro Pump,Ice Beam,Earthquake,Volt Switch|EVs: 252 SpA / 252 Spe / 4 HP|Timid|
Venusaur|Assault Vest|Chlorophyll||Giga Drain,Sludge Bomb,Earthquake,Sleep Powder|EVs: 252 SpA / 252 Spe / 4 HP|Timid|
Lapras|Assault Vest|Water Absorb||Hydro Pump,Ice Beam,Thunderbolt,Recover|EVs: 252 SpA / 252 Spe / 4 HP|Timid|
Gyarados|Assault Vest|Intimidate||Earthquake,Waterfall,Stone Edge,Crunch|EVs: 252 Atk / 252 Spe / 4 HP|Adamant|
TEAM;
    }

    /**
     * List all active battles on Showdown
     */
    public function listActiveBattles(): array
    {
        try {
            return $this->client->listBattles();
        } catch (Exception $e) {
            \Log::error("Failed to list battles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse battle action string
     *
     * Examples:
     * - '>move 1' - Use move 1
     * - '>switch 2' - Switch to Pokémon 2
     * - '>pass' - Pass turn
     */
    public static function buildAction(string $type, int|string $value): string
    {
        return match($type) {
            'move' => ">move $value",
            'switch' => ">switch $value",
            'pass' => '>pass',
            default => ">move $value",
        };
    }
}
