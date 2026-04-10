<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('championship_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('team_sport_modes')->restrictOnDelete();
            $table->foreignId('away_team_id')->constrained('team_sport_modes')->restrictOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('location', 255)->nullable();
            $table->enum('match_status', ['scheduled', 'completed', 'cancelled', 'postponed'])
                ->default('scheduled');
            $table->integer('home_goals')->nullable();
            $table->integer('away_goals')->nullable();
            $table->integer('home_penalties')->nullable();
            $table->integer('away_penalties')->nullable();
            $table->integer('leg')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_matches');
    }
};
