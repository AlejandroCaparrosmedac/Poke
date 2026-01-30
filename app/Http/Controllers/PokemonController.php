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
     * Mostrar listado de Pokémon con paginación.
     */
    public function index(): View
    {
        $page = request('page', 1);
        $limit = 20;

        $result = $this->pokemonService->getPokemonList($page, $limit);

        if (isset($result['error'])) {
            return view('pokemon.index', ['error' => $result['error']]);
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
