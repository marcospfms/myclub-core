<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $table = 'teams';

    protected $fillable = ['owner_id', 'name', 'description', 'badge', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sportModes(): HasMany
    {
        return $this->hasMany(TeamSportMode::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(TeamStaff::class);
    }
}
