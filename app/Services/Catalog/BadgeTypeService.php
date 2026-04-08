<?php

namespace App\Services\Catalog;

use App\Models\BadgeType;
use Illuminate\Database\Eloquent\Collection;

class BadgeTypeService
{
    public function listAll(): Collection
    {
        return BadgeType::query()->orderBy('name')->get();
    }

    public function create(array $data): BadgeType
    {
        return BadgeType::create($data);
    }

    public function update(BadgeType $badgeType, array $data): BadgeType
    {
        $badgeType->update($data);

        return $badgeType->fresh();
    }

    public function delete(BadgeType $badgeType): void
    {
        $badgeType->delete();
    }
}
