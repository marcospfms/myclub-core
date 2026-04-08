<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = ['key', 'name'];

    public function sportModes(): BelongsToMany
    {
        return $this->belongsToMany(SportMode::class, 'sport_mode_category')->withTimestamps();
    }
}
