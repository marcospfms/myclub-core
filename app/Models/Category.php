<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = ['key', 'name'];

    public function sportModes(): BelongsToMany
    {
        return $this->belongsToMany(SportMode::class, 'sport_mode_category')->withTimestamps();
    }

    public function championships(): HasMany
    {
        return $this->hasMany(Championship::class);
    }
}
