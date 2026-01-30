<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class PokemonService
{
    private const BASE_URL = 'https://pokeapi.co/api/v2';
    private const CACHE_MINUTES = 60 * 24; // 24 horas

    /**
     * Obtener lista de Pokémon con paginación.
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getPokemonList($page = 1, $limit = 20)
    {
        try {
            $offset = ($page - 1) * $limit;
            $cacheKey = "pokemon_list_{$offset}_{$limit}";

            return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($offset, $limit) {
                $response = Http::get(self::BASE_URL . "/pokemon", [
                    'offset' => $offset,
                    'limit' => $limit,
                ]);

                if ($response->failed()) {
                    throw new Exception('Error fetching Pokémon list from API');
                }

                $data = $response->json();

                // Enriquecer los datos con imágenes
                $pokemonList = [];
                foreach ($data['results'] as $pokemon) {
                    $pokemonList[] = [
                        'name' => $pokemon['name'],
                        'url' => $pokemon['url'],
                        'id' => $this->extractIdFromUrl($pokemon['url']),
                        'image' => $this->getPokemonImageUrl($this->extractIdFromUrl($pokemon['url'])),
                    ];
                }

                return [
                    'pokemon' => $pokemonList,
                    'total' => $data['count'],
                    'current_page' => ceil($offset / $limit) + 1,
                    'per_page' => $limit,
                    'last_page' => ceil($data['count'] / $limit),
                ];
            });
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'pokemon' => [],
                'total' => 0,
            ];
        }
    }

    /**
     * Obtener detalles completos de un Pokémon.
     *
     * @param int|string $pokemonId
     * @return array
     */
    public function getPokemonDetail($pokemonId)
    {
        try {
            $cacheKey = "pokemon_detail_{$pokemonId}";

            return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($pokemonId) {
                $response = Http::get(self::BASE_URL . "/pokemon/{$pokemonId}");

                if ($response->failed()) {
                    throw new Exception("Pokémon not found: {$pokemonId}");
                }

                $data = $response->json();

                return [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'image' => $data['sprites']['other']['official-artwork']['front_default'] ?? $data['sprites']['front_default'],
                    'types' => array_map(function ($type) {
                        return $type['type']['name'];
                    }, $data['types']),
                    'abilities' => array_map(function ($ability) {
                        return [
                            'name' => $ability['ability']['name'],
                            'is_hidden' => $ability['is_hidden'],
                        ];
                    }, $data['abilities']),
                    'stats' => array_map(function ($stat) {
                        return [
                            'name' => $stat['stat']['name'],
                            'base_stat' => $stat['base_stat'],
                        ];
                    }, $data['stats']),
                    'height' => $data['height'] / 10, // Convertir a metros
                    'weight' => $data['weight'] / 10, // Convertir a kg
                ];
            });
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Extraer ID de Pokémon desde la URL de la API.
     *
     * @param string $url
     * @return int
     */
    private function extractIdFromUrl($url)
    {
        preg_match('/\/(\d+)\/$/', $url, $matches);
        return $matches[1] ?? 0;
    }

    /**
     * Obtener URL de imagen de un Pokémon.
     *
     * @param int $id
     * @return string
     */
    private function getPokemonImageUrl($id)
    {
        return "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$id}.png";
    }

    /**
     * Buscar Pokémon por nombre.
     *
     * @param string $name
     * @return array
     */
    public function searchPokemon($name)
    {
        try {
            $response = Http::get(self::BASE_URL . "/pokemon/{$name}");

            if ($response->failed()) {
                return ['error' => 'Pokémon not found'];
            }

            $data = $response->json();

            return [
                'id' => $data['id'],
                'name' => $data['name'],
                'image' => $data['sprites']['other']['official-artwork']['front_default'] ?? $data['sprites']['front_default'],
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
