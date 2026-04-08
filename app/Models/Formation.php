<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Formation extends Model
{
    protected $table = 'formations';

    protected $fillable = ['key', 'name'];

    public function sportModes(): BelongsToMany
    {
        return $this->belongsToMany(SportMode::class, 'sport_mode_formation')->withTimestamps();
    }
}
