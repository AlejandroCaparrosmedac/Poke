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
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['pvp', 'pve']); // Player vs Player or Player vs Environment
            $table->enum('format', ['singles', 'doubles'])->default('singles');
            $table->enum('status', ['pending', 'active', 'finished'])->default('pending');
            $table->string('showdown_id')->nullable()->unique(); // ID from Showdown microservice
            $table->string('showdown_room_id')->nullable(); // Room ID for Showdown
            $table->string('winner_id')->nullable(); // User ID of winner (null if draw)
            $table->text('replay_log')->nullable(); // JSON: battle replay/log
            $table->timestamps();
            $table->index(['status', 'type']);
            $table->index('showdown_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
