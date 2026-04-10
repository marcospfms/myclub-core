<?php

namespace App\Models;

use App\Enums\AwardType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChampionshipAward extends Model
{
    use HasFactory;

    protected $table = 'championship_awards';

    protected $fillable = [
        'championship_id',
        'player_id',
        'award_type',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'award_type' => AwardType::class,
        ];
    }

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'user_id');
    }
}
