<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_sport_mode_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('player_id');
            $table->foreign('player_id')->references('user_id')->on('players')->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_starter')->default(false);
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_active_membership')->nullable()->storedAs('case when `left_at` is null then 1 else null end');
            $table->unique(
                ['team_sport_mode_id', 'player_id', 'is_active_membership'],
                'player_memberships_active_unique'
            );
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_memberships');
    }
};
