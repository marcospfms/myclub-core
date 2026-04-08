<?php

namespace App\Services\Catalog;

use App\Models\Position;
use Illuminate\Database\Eloquent\Collection;

class PositionService
{
    public function listAll(): Collection
    {
        return Position::query()->orderBy('key')->get();
    }

    public function create(array $data): Position
    {
        return Position::create($data);
    }

    public function update(Position $position, array $data): Position
    {
        $position->update($data);

        return $position->fresh();
    }

    public function delete(Position $position): void
    {
        $position->delete();
    }
}
