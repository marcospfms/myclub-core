<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_match_highlights', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('championship_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_membership_id')->constrained()->restrictOnDelete();
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('yellow_cards')->default(0);
            $table->integer('red_cards')->default(0);
            $table->boolean('is_mvp')->default(false);
            $table->unique(['championship_match_id', 'player_membership_id'], 'cmh_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_match_highlights');
    }
};
