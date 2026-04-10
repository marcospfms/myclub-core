<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_phases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->enum('type', ['group_stage', 'knockout'])->default('group_stage');
            $table->integer('phase_order')->default(1);
            $table->integer('legs')->default(1);
            $table->integer('advances_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_phases');
    }
};
