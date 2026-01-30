<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('pokemon_id'); // ID de la PokeAPI
            $table->string('pokemon_name'); // Nombre del Pokémon
            $table->string('pokemon_image'); // URL de la imagen
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->unique(['user_id', 'pokemon_id']); // Un usuario no puede marcar el mismo Pokémon dos veces
            $table->index('pokemon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
