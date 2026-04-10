<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlayerBadge extends Model
{
    use HasFactory;

    protected $table = 'player_badges';

    protected $fillable = [
        'player_id',
        'badge_type_id',
        'championship_id',
        'awarded_at',
        'notes',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'user_id');
    }

    public function badgeType(): BelongsTo
    {
        return $this->belongsTo(BadgeType::class);
    }

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class)->withDefault();
    }
}
