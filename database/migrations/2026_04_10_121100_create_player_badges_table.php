<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_badges', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->foreign('player_id')->references('user_id')->on('players')->cascadeOnDelete();
            $table->foreignId('badge_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('championship_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('awarded_at');
            $table->string('notes', 255)->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_badges');
    }
};
