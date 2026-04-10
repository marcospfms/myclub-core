<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_sport_modes', function (Blueprint $table): void {
            $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sport_mode_id')->constrained()->restrictOnDelete();
            $table->primary(['championship_id', 'sport_mode_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_sport_modes');
    }
};
