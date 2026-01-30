<?php

namespace Database\Factories;

use App\Models\Battle;
use App\Models\BattlePlayer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BattlePlayer>
 */
class BattlePlayerFactory extends Factory
{
    protected $model = BattlePlayer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'battle_id' => Battle::factory(),
            'user_id' => User::factory(),
            'team_id' => null,
            'player_slot' => $this->faker->randomElement(['p1', 'p2']),
            'is_ai' => false,
            'is_winner' => null,
            'current_turn' => 0,
        ];
    }

    /**
     * Create an AI player.
     */
    public function aiPlayer(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'team_id' => null,
            'is_ai' => true,
        ]);
    }
}
