<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('staff_member_id');
            $table->foreign('staff_member_id')->references('user_id')->on('staff_members')->cascadeOnDelete();
            $table->unique(['team_id', 'staff_member_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_staff');
    }
};
