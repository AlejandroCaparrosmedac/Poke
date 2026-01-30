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
        Schema::create('turn_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('battle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('battle_player_id')->constrained()->cascadeOnDelete();
            $table->integer('turn_number');
            $table->string('decision_type'); // 'move', 'switch', 'forfeit'
            $table->text('decision_data'); // JSON: {move: '...', pokemonSlot: ..., etc}
            $table->text('decision_result')->nullable(); // JSON: response from Showdown
            $table->enum('status', ['pending', 'executed', 'failed'])->default('pending');
            $table->timestamps();
            $table->unique(['battle_id', 'battle_player_id', 'turn_number']);
            $table->index(['battle_id', 'turn_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turn_decisions');
    }
};
