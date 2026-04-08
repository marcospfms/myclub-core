<?php

namespace App\Services\Catalog;

use App\Models\SportMode;
use Illuminate\Database\Eloquent\Collection;

class SportModeService
{
    public function listAll(): Collection
    {
        return SportMode::with(['categories', 'formations', 'positions'])
            ->orderBy('key')
            ->get();
    }

    public function create(array $data): SportMode
    {
        return SportMode::create($data);
    }

    public function update(SportMode $sportMode, array $data): SportMode
    {
        $sportMode->update($data);

        return $sportMode->fresh(['categories', 'formations', 'positions']);
    }

    public function delete(SportMode $sportMode): void
    {
        $sportMode->delete();
    }

    public function syncCategories(SportMode $sportMode, array $categoryIds): void
    {
        $sportMode->categories()->sync($categoryIds);
    }

    public function syncFormations(SportMode $sportMode, array $formationIds): void
    {
        $sportMode->formations()->sync($formationIds);
    }

    public function syncPositions(SportMode $sportMode, array $positionIds): void
    {
        $sportMode->positions()->sync($positionIds);
    }
}
