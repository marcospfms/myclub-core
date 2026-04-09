<?php

namespace App\Models;

use App\Enums\BadgeScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BadgeType extends Model
{
    use HasFactory;

    protected $table = 'badge_types';

    protected $fillable = ['name', 'label_key', 'description_key', 'icon', 'scope'];

    protected function casts(): array
    {
        return [
            'scope' => BadgeScope::class,
        ];
    }
}
