<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PokemonController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\BattleShowdownController;

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

    // Batallas Pokémon Showdown
    Route::get('/battles', [BattleShowdownController::class, 'index'])->name('battles.index');
    Route::get('/battles/create', [BattleShowdownController::class, 'create'])->name('battles.create');
    Route::post('/battles', [BattleShowdownController::class, 'store'])->name('battles.store');
    Route::get('/battles/{battleId}', [BattleShowdownController::class, 'show'])->name('battles.show');
    Route::post('/battles/{battleId}/move', [BattleShowdownController::class, 'submitMove'])->name('battles.move');
    Route::get('/battles/{battleId}/logs', [BattleShowdownController::class, 'getLogs'])->name('battles.logs');
    Route::post('/battles/{battleId}/finish', [BattleShowdownController::class, 'finish'])->name('battles.finish');
    Route::get('/api/battles', [BattleShowdownController::class, 'listBattles'])->name('battles.list');
});
