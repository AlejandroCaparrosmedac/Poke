<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Services\PokemonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    private PokemonService $pokemonService;

    public function __construct(PokemonService $pokemonService)
    {
        $this->pokemonService = $pokemonService;
        $this->middleware('auth'); // Solo usuarios autenticados
    }

    /**
     * Mostrar lista de Pokémon favoritos del usuario.
     */
    public function index(): View
    {
        $favorites = auth()->user()->favorites()->paginate(20);

        return view('favorites.index', [
            'favorites' => $favorites,
        ]);
    }

    /**
     * Agregar Pokémon a favoritos.
     */
    public function store(): RedirectResponse
    {
        $pokemonId = request('pokemon_id');
        $pokemonName = request('pokemon_name');
        $pokemonImage = request('pokemon_image');

        // Validar que no sea duplicado
        $exists = auth()->user()->favorites()
            ->where('pokemon_id', $pokemonId)
            ->exists();

        if ($exists) {
            return back()->with('warning', 'Este Pokémon ya está en tus favoritos.');
        }

        // Crear favorito
        Favorite::create([
            'user_id' => auth()->id(),
            'pokemon_id' => $pokemonId,
            'pokemon_name' => $pokemonName,
            'pokemon_image' => $pokemonImage,
        ]);

        return back()->with('success', 'Pokémon agregado a favoritos.');
    }

    /**
     * Eliminar Pokémon de favoritos.
     */
    public function destroy(Favorite $favorite): RedirectResponse
    {
        // Verificar que el favorito pertenece al usuario autenticado
        if ($favorite->user_id !== auth()->id()) {
            return back()->with('error', 'No tienes permiso para eliminar este favorito.');
        }

        $favorite->delete();

        return back()->with('success', 'Pokémon eliminado de favoritos.');
    }

    /**
     * Eliminar favorito por Pokémon ID (para llamadas AJAX).
     */
    public function destroyByPokemon($pokemonId): RedirectResponse
    {
        $favorite = auth()->user()->favorites()
            ->where('pokemon_id', $pokemonId)
            ->first();

        if (!$favorite) {
            return back()->with('error', 'Favorito no encontrado.');
        }

        $favorite->delete();

        return back()->with('success', 'Pokémon eliminado de favoritos.');
    }
}
