<?php

namespace App\Services\Catalog;

use App\Models\Formation;
use Illuminate\Database\Eloquent\Collection;

class FormationService
{
    public function listAll(): Collection
    {
        return Formation::query()->orderBy('name')->get();
    }

    public function create(array $data): Formation
    {
        return Formation::create($data);
    }

    public function update(Formation $formation, array $data): Formation
    {
        $formation->update($data);

        return $formation->fresh();
    }

    public function delete(Formation $formation): void
    {
        $formation->delete();
    }
}
