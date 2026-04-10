<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('championship_phase_id')->constrained()->cascadeOnDelete();
            $table->string('name', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_groups');
    }
};
