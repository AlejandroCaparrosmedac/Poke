<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(2, true),
            'pokemon_data' => [
                [
                    'name' => 'Pikachu',
                    'level' => 50,
                    'moves' => ['Thunderbolt', 'Quick Attack'],
                ],
                [
                    'name' => 'Charizard',
                    'level' => 50,
                    'moves' => ['Flamethrower', 'Dragon Claw'],
                ],
            ],
        ];
    }
}
