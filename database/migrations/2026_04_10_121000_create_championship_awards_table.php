<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_awards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('player_id');
            $table->foreign('player_id')->references('user_id')->on('players')->restrictOnDelete();
            $table->enum('award_type', ['golden_ball', 'top_scorer', 'best_assist', 'best_goalkeeper', 'fair_play']);
            $table->integer('value')->nullable();
            $table->unique(['championship_id', 'award_type']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_awards');
    }
};
