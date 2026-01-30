<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PokemonController;
use App\Http\Controllers\FavoriteController;

// Ruta raíz: redireccionar a login si no está autenticado
Route::get('/', function () {
    return auth()->check() ? redirect('/pokemon') : redirect('/login');
});

// ========== RUTAS DE AUTENTICACIÓN (Guests) ==========
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ========== RUTAS PROTEGIDAS (Autenticadas) ==========
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Pokémon
    Route::get('/pokemon', [PokemonController::class, 'index'])->name('pokemon.index');
    Route::get('/pokemon/{id}', [PokemonController::class, 'show'])->name('pokemon.show');

    // Favoritos
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::delete('/favorites/pokemon/{pokemonId}', [FavoriteController::class, 'destroyByPokemon'])->name('favorites.destroy-by-pokemon');
});
