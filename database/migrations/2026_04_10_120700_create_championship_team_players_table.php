<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_team_players', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_sport_mode_id')->constrained()->restrictOnDelete();
            $table->foreignId('player_membership_id')->constrained()->restrictOnDelete();
            $table->unique(['championship_id', 'team_sport_mode_id', 'player_membership_id'], 'ctp_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_team_players');
    }
};
