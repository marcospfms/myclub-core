<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamSportMode extends Model
{
    use HasFactory;

    protected $table = 'team_sport_modes';

    protected $fillable = ['team_id', 'sport_mode_id'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function sportMode(): BelongsTo
    {
        return $this->belongsTo(SportMode::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(PlayerMembership::class);
    }

    public function activeMemberships(): HasMany
    {
        return $this->hasMany(PlayerMembership::class)
            ->whereNull('left_at');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }
}
