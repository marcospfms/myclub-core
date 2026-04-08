<?php

namespace App\Services\Catalog;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function listAll(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
