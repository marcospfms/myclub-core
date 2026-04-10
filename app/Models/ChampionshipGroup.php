<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChampionshipGroup extends Model
{
    use HasFactory;

    protected $table = 'championship_groups';

    protected $fillable = [
        'championship_phase_id',
        'name',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ChampionshipPhase::class, 'championship_phase_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ChampionshipGroupEntry::class);
    }
}
