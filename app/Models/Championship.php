<?php

namespace App\Models;

use App\Enums\ChampionshipFormat;
use App\Enums\ChampionshipStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Championship extends Model
{
    use HasFactory;

    protected $table = 'championships';

    protected $fillable = [
        'created_by',
        'category_id',
        'name',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'format',
        'status',
        'max_players',
    ];

    protected function casts(): array
    {
        return [
            'format' => ChampionshipFormat::class,
            'status' => ChampionshipStatus::class,
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sportModes(): BelongsToMany
    {
        return $this->belongsToMany(SportMode::class, 'championship_sport_modes')
            ->withTimestamps();
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ChampionshipPhase::class)->orderBy('phase_order');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(ChampionshipTeam::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(ChampionshipAward::class);
    }

    public function playerBadges(): HasMany
    {
        return $this->hasMany(PlayerBadge::class);
    }

    public function isDraft(): bool
    {
        return $this->status === ChampionshipStatus::Draft;
    }

    public function isEnrollment(): bool
    {
        return $this->status === ChampionshipStatus::Enrollment;
    }

    public function isActive(): bool
    {
        return $this->status === ChampionshipStatus::Active;
    }

    public function isFinished(): bool
    {
        return $this->status === ChampionshipStatus::Finished;
    }
}
