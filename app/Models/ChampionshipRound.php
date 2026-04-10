<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChampionshipRound extends Model
{
    use HasFactory;

    protected $table = 'championship_rounds';

    protected $fillable = [
        'championship_phase_id',
        'name',
        'round_number',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ChampionshipPhase::class, 'championship_phase_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ChampionshipMatch::class);
    }
}
