<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceHighlight extends Model
{
    use HasFactory;

    protected $table = 'performance_highlights';

    protected $fillable = [
        'friendly_match_id',
        'player_membership_id',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
    ];

    protected function casts(): array
    {
        return [
            'goals' => 'integer',
            'assists' => 'integer',
            'yellow_cards' => 'integer',
            'red_cards' => 'integer',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(FriendlyMatch::class, 'friendly_match_id');
    }

    public function playerMembership(): BelongsTo
    {
        return $this->belongsTo(PlayerMembership::class);
    }
}
