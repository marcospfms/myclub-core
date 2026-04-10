<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChampionshipMatchHighlight extends Model
{
    use HasFactory;

    protected $table = 'championship_match_highlights';

    protected $fillable = [
        'championship_match_id',
        'player_membership_id',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
        'is_mvp',
    ];

    protected function casts(): array
    {
        return [
            'goals' => 'integer',
            'assists' => 'integer',
            'yellow_cards' => 'integer',
            'red_cards' => 'integer',
            'is_mvp' => 'boolean',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(ChampionshipMatch::class, 'championship_match_id');
    }

    public function playerMembership(): BelongsTo
    {
        return $this->belongsTo(PlayerMembership::class);
    }
}
