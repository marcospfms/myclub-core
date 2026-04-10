<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChampionshipTeamPlayer extends Model
{
    use HasFactory;

    protected $table = 'championship_team_players';

    protected $fillable = [
        'championship_id',
        'team_sport_mode_id',
        'player_membership_id',
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function teamSportMode(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(PlayerMembership::class, 'player_membership_id');
    }
}
