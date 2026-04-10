<?php

namespace App\Models;

use App\Enums\PhaseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChampionshipPhase extends Model
{
    use HasFactory;

    protected $table = 'championship_phases';

    protected $fillable = [
        'championship_id',
        'name',
        'type',
        'phase_order',
        'legs',
        'advances_count',
    ];

    protected function casts(): array
    {
        return [
            'type' => PhaseType::class,
        ];
    }

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(ChampionshipGroup::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(ChampionshipRound::class)->orderBy('round_number');
    }
}
