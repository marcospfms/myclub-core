<?php

namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChampionshipMatch extends Model
{
    use HasFactory;

    protected $table = 'championship_matches';

    protected $fillable = [
        'championship_round_id',
        'home_team_id',
        'away_team_id',
        'scheduled_at',
        'location',
        'match_status',
        'home_goals',
        'away_goals',
        'home_penalties',
        'away_penalties',
        'leg',
    ];

    protected function casts(): array
    {
        return [
            'match_status' => MatchStatus::class,
            'scheduled_at' => 'datetime',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(ChampionshipRound::class, 'championship_round_id');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class, 'away_team_id');
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(ChampionshipMatchHighlight::class);
    }

    public function isCompleted(): bool
    {
        return $this->match_status === MatchStatus::Completed;
    }
}
