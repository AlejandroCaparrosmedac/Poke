<?php

namespace Database\Factories;

use App\Models\Battle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Battle>
 */
class BattleFactory extends Factory
{
    protected $model = Battle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['pvp', 'pve']),
            'format' => $this->faker->randomElement(['singles', 'doubles']),
            'status' => $this->faker->randomElement(['pending', 'active', 'finished']),
            'showdown_id' => $this->faker->uuid(),
            'showdown_room_id' => 'battle-' . $this->faker->randomNumber(),
            'winner_id' => null,
        ];
    }

    /**
     * Battle is finished with a winner.
     */
    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finished',
            'winner_id' => rand(1, 10),
        ]);
    }
}
