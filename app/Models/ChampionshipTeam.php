<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChampionshipTeam extends Model
{
    use HasFactory;

    protected $table = 'championship_teams';

    protected $fillable = [
        'championship_id',
        'team_sport_mode_id',
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function teamSportMode(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(ChampionshipTeamPlayer::class, 'team_sport_mode_id', 'team_sport_mode_id')
            ->where('championship_id', $this->championship_id);
    }
}
