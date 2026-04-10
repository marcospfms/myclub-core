<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChampionshipGroupEntry extends Model
{
    use HasFactory;

    protected $table = 'championship_group_entries';

    protected $fillable = [
        'championship_group_id',
        'team_sport_mode_id',
        'final_position',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ChampionshipGroup::class, 'championship_group_id');
    }

    public function teamSportMode(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class);
    }
}
