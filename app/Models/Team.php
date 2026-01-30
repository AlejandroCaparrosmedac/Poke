<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'pokemon_data',
    ];

    protected $casts = [
        'pokemon_data' => 'array',
    ];

    /**
     * A team belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A team can participate in many battles.
     */
    public function battles(): HasMany
    {
        return $this->hasMany(BattlePlayer::class);
    }

    /**
     * Get the Pokémon in this team as an array.
     */
    public function getPokemons(): array
    {
        return $this->pokemon_data ?? [];
    }

    /**
     * Add a Pokémon to the team.
     */
    public function addPokemon(array $pokemonData): void
    {
        $pokemons = $this->getPokemons();
        $pokemons[] = $pokemonData;
        $this->pokemon_data = $pokemons;
        $this->save();
    }
}
