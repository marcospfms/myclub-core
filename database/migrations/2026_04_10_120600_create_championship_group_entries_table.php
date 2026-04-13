<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_group_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('championship_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_sport_mode_id')->constrained()->restrictOnDelete();
            $table->integer('final_position')->nullable();
            $table->unique(['championship_group_id', 'team_sport_mode_id'], 'cge_group_team_mode_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_group_entries');
    }
};
