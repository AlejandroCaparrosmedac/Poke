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
        Schema::create('battle_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('battle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete(); // Nullable for AI players
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('player_slot'); // 'p1' or 'p2'
            $table->boolean('is_ai')->default(false); // True if this is a PvE AI opponent
            $table->boolean('is_winner')->nullable(); // null = draw, true = won, false = lost
            $table->integer('current_turn')->default(0);
            $table->timestamps();
            $table->unique(['battle_id', 'player_slot']);
            $table->index(['user_id', 'battle_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_players');
    }
};
