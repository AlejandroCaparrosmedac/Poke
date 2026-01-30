<?php

namespace App\Http\Controllers;

use App\Services\PokemonService;
use Illuminate\View\View;

class PokemonController extends Controller
{
    private PokemonService $pokemonService;

    public function __construct(PokemonService $pokemonService)
    {
        $this->middleware('auth'); // Solo usuarios autenticados
        $this->pokemonService = $pokemonService;
    }

    /**
     * Mostrar listado de Pokémon con paginación, búsqueda y filtros.
     */
    public function index(): View
    {
        $page = request('page', 1);
        $search = request('search');
        $type = request('type');
        $generation = request('generation');
        $limit = 20;

        // Obtener datos con filtros
        $result = $this->pokemonService->filterPokemon($page, $limit, $search, $type, $generation);

        if (isset($result['error'])) {
            return view('pokemon.index', [
                'error' => $result['error'],
                'types' => $this->pokemonService->getTypes(),
                'generations' => $this->pokemonService->getGenerations(),
            ]);
        }

        // Obtener IDs de favoritos del usuario
        $favoriteIds = auth()->user()->favorites()->pluck('pokemon_id')->toArray();

        return view('pokemon.index', [
            'pokemon' => $result['pokemon'],
            'total' => $result['total'],
            'current_page' => $result['current_page'],
            'last_page' => $result['last_page'],
            'per_page' => $result['per_page'],
            'favoriteIds' => $favoriteIds,
            'types' => $this->pokemonService->getTypes(),
            'generations' => $this->pokemonService->getGenerations(),
            'activeSearch' => $search,
            'activeType' => $type,
            'activeGeneration' => $generation,
        ]);
    }

    /**
     * Mostrar detalle de un Pokémon.
     */
    public function show($id): View
    {
        $pokemon = $this->pokemonService->getPokemonDetail($id);

        if (isset($pokemon['error'])) {
            return back()->with('error', $pokemon['error']);
        }

        // Verificar si es favorito
        $isFavorite = auth()->user()->isFavorite($id);

        return view('pokemon.show', [
            'pokemon' => $pokemon,
            'isFavorite' => $isFavorite,
        ]);
    }
}
