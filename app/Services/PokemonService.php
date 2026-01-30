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
     * Buscar Pokémon por nombre o ID.
     *
     * @param string $query
     * @return array
     */
    public function searchPokemon($query)
    {
        try {
            $response = Http::get(self::BASE_URL . "/pokemon/{$query}");

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

    /**
     * Obtener todos los tipos de Pokémon.
     *
     * @return array
     */
    public function getTypes()
    {
        try {
            $cacheKey = 'pokemon_types';

            return Cache::remember($cacheKey, self::CACHE_MINUTES, function () {
                $response = Http::get(self::BASE_URL . '/type');

                if ($response->failed()) {
                    return [];
                }

                $data = $response->json();
                $types = [];
                foreach ($data['results'] as $type) {
                    $types[] = [
                        'id' => $this->extractIdFromUrl($type['url']),
                        'name' => $type['name'],
                    ];
                }

                return $types;
            });
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtener Pokémon por tipo.
     *
     * @param string $type
     * @param int $limit
     * @return array
     */
    public function getPokemonByType($type, $limit = 20)
    {
        try {
            $cacheKey = "pokemon_type_{$type}";

            return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($type, $limit) {
                $response = Http::get(self::BASE_URL . "/type/{$type}");

                if ($response->failed()) {
                    return [];
                }

                $data = $response->json();
                $pokemonList = [];

                foreach (array_slice($data['pokemon'], 0, $limit) as $item) {
                    $pokemon = $item['pokemon'];
                    $pokemonList[] = [
                        'name' => $pokemon['name'],
                        'id' => $this->extractIdFromUrl($pokemon['url']),
                        'image' => $this->getPokemonImageUrl($this->extractIdFromUrl($pokemon['url'])),
                    ];
                }

                return $pokemonList;
            });
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtener generaciones disponibles.
     *
     * @return array
     */
    public function getGenerations()
    {
        try {
            $cacheKey = 'pokemon_generations';

            return Cache::remember($cacheKey, self::CACHE_MINUTES, function () {
                $response = Http::get(self::BASE_URL . '/generation');

                if ($response->failed()) {
                    return [];
                }

                $data = $response->json();
                $generations = [];
                foreach ($data['results'] as $gen) {
                    $generations[] = [
                        'id' => $this->extractIdFromUrl($gen['url']),
                        'name' => $gen['name'],
                    ];
                }

                return $generations;
            });
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtener Pokémon de una generación específica.
     *
     * @param int $generationId
     * @param int $limit
     * @return array
     */
    public function getPokemonByGeneration($generationId, $limit = 20)
    {
        try {
            $cacheKey = "pokemon_generation_{$generationId}";

            return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($generationId, $limit) {
                $response = Http::get(self::BASE_URL . "/generation/{$generationId}");

                if ($response->failed()) {
                    return [];
                }

                $data = $response->json();
                $pokemonList = [];

                foreach (array_slice($data['pokemon_species'], 0, $limit) as $species) {
                    $id = $this->extractIdFromUrl($species['url']);
                    $pokemonList[] = [
                        'name' => $species['name'],
                        'id' => $id,
                        'image' => $this->getPokemonImageUrl($id),
                    ];
                }

                return $pokemonList;
            });
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Filtrar Pokémon por criterios múltiples.
     *
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @param string|null $type
     * @param int|null $generation
     * @return array
     */
    public function filterPokemon($page = 1, $limit = 20, $search = null, $type = null, $generation = null)
    {
        try {
            // Si hay búsqueda, buscar Pokémon específico
            if ($search) {
                // Intentar buscar por nombre o ID
                $result = $this->searchPokemon(strtolower($search));
                if (!isset($result['error'])) {
                    return [
                        'pokemon' => [$result],
                        'total' => 1,
                        'current_page' => 1,
                        'per_page' => 1,
                        'last_page' => 1,
                    ];
                }
            }

            // Si hay filtro de tipo
            if ($type) {
                $pokemonList = $this->getPokemonByType($type, 100);
                $total = count($pokemonList);
                $lastPage = ceil($total / $limit);
                $offset = ($page - 1) * $limit;

                return [
                    'pokemon' => array_slice($pokemonList, $offset, $limit),
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $limit,
                    'last_page' => $lastPage,
                ];
            }

            // Si hay filtro de generación
            if ($generation) {
                $pokemonList = $this->getPokemonByGeneration($generation, 100);
                $total = count($pokemonList);
                $lastPage = ceil($total / $limit);
                $offset = ($page - 1) * $limit;

                return [
                    'pokemon' => array_slice($pokemonList, $offset, $limit),
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $limit,
                    'last_page' => $lastPage,
                ];
            }

            // Si no hay filtros, retornar lista normal
            return $this->getPokemonList($page, $limit);
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'pokemon' => [],
                'total' => 0,
            ];
        }
    }
}
