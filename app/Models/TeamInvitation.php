<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamInvitation extends Model
{
    use HasFactory;

    protected $table = 'team_invitations';

    protected $fillable = [
        'team_sport_mode_id',
        'invited_user_id',
        'invited_by',
        'position_id',
        'status',
        'expires_at',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'expires_at' => 'datetime',
        ];
    }

    public function teamSportMode(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class);
    }

    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function isPending(): bool
    {
        return $this->status === InvitationStatus::Pending;
    }
}
