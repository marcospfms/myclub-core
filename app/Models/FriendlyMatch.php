<?php

namespace App\Models;

use App\Enums\MatchStatus;
use App\Enums\ResultStatus;
use App\Enums\MatchConfirmation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FriendlyMatch extends Model
{
    use HasFactory;

    protected $table = 'friendly_matches';

    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'scheduled_at',
        'location',
        'confirmation',
        'invite_expires_at',
        'match_status',
        'home_goals',
        'away_goals',
        'home_notes',
        'away_notes',
        'is_public',
        'result_status',
        'result_registered_by',
    ];

    protected function casts(): array
    {
        return [
            'confirmation' => MatchConfirmation::class,
            'match_status' => MatchStatus::class,
            'result_status' => ResultStatus::class,
            'scheduled_at' => 'datetime',
            'invite_expires_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class, 'away_team_id');
    }

    public function resultRegisteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'result_registered_by');
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(PerformanceHighlight::class);
    }

    public function isPending(): bool
    {
        return $this->confirmation === MatchConfirmation::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmation === MatchConfirmation::Confirmed;
    }

    public function isCompleted(): bool
    {
        return $this->match_status === MatchStatus::Completed;
    }
}
