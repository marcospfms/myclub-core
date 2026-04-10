<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlayerMembership extends Model
{
    use HasFactory;

    protected $table = 'player_memberships';

    protected $fillable = ['team_sport_mode_id', 'player_id', 'position_id', 'is_starter', 'left_at'];

    protected function casts(): array
    {
        return [
            'is_starter' => 'boolean',
            'left_at' => 'datetime',
        ];
    }

    public function teamSportMode(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'user_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function championshipSelections(): HasMany
    {
        return $this->hasMany(ChampionshipTeamPlayer::class, 'player_membership_id');
    }

    public function championshipHighlights(): HasMany
    {
        return $this->hasMany(ChampionshipMatchHighlight::class, 'player_membership_id');
    }
}
