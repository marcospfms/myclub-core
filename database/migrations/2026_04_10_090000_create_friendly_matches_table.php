<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friendly_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('home_team_id')->constrained('team_sport_modes')->restrictOnDelete();
            $table->foreignId('away_team_id')->constrained('team_sport_modes')->restrictOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('location', 255)->nullable();
            $table->enum('confirmation', ['pending', 'confirmed', 'rejected', 'expired'])->default('pending');
            $table->timestamp('invite_expires_at')->nullable();
            $table->enum('match_status', ['scheduled', 'completed', 'cancelled', 'postponed'])->nullable();
            $table->integer('home_goals')->nullable();
            $table->integer('away_goals')->nullable();
            $table->text('home_notes')->nullable();
            $table->text('away_notes')->nullable();
            $table->boolean('is_public')->default(false);
            $table->enum('result_status', ['none', 'pending', 'confirmed', 'disputed'])->default('none');
            $table->foreignId('result_registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendly_matches');
    }
};
