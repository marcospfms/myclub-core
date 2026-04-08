<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    protected $table = 'positions';

    protected $fillable = ['key', 'label_key', 'description_key', 'icon', 'abbreviation'];

    public function sportModes(): BelongsToMany
    {
        return $this->belongsToMany(SportMode::class, 'sport_mode_position')->withTimestamps();
    }
}
