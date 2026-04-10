<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SportMode extends Model
{
    use HasFactory;

    protected $table = 'sport_modes';

    protected $fillable = ['key', 'label_key', 'description_key', 'icon'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'sport_mode_category')->withTimestamps();
    }

    public function formations(): BelongsToMany
    {
        return $this->belongsToMany(Formation::class, 'sport_mode_formation')->withTimestamps();
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'sport_mode_position')->withTimestamps();
    }

    public function teamSportModes(): HasMany
    {
        return $this->hasMany(TeamSportMode::class);
    }

    public function championships(): BelongsToMany
    {
        return $this->belongsToMany(Championship::class, 'championship_sport_modes')->withTimestamps();
    }
}
