<?php

namespace App\Services\Catalog;

use App\Models\StaffRole;
use Illuminate\Database\Eloquent\Collection;

class StaffRoleService
{
    public function listAll(): Collection
    {
        return StaffRole::query()->orderBy('name')->get();
    }

    public function create(array $data): StaffRole
    {
        return StaffRole::create($data);
    }

    public function update(StaffRole $staffRole, array $data): StaffRole
    {
        $staffRole->update($data);

        return $staffRole->fresh();
    }

    public function delete(StaffRole $staffRole): void
    {
        $staffRole->delete();
    }
}
