<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Model
{
    use HasFactory;

    protected $table = 'players';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'cpf',
        'rg',
        'birth_date',
        'phone',
        'is_discoverable',
        'history_public',
        'city',
        'state',
        'country',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_discoverable' => 'boolean',
            'history_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(PlayerMembership::class, 'player_id', 'user_id');
    }

    public function activeMemberships(): HasMany
    {
        return $this->hasMany(PlayerMembership::class, 'player_id', 'user_id')
            ->whereNull('left_at');
    }

    public function championshipAwards(): HasMany
    {
        return $this->hasMany(ChampionshipAward::class, 'player_id', 'user_id');
    }

    public function badges(): HasMany
    {
        return $this->hasMany(PlayerBadge::class, 'player_id', 'user_id');
    }
}
