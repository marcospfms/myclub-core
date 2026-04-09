<?php

namespace App\Http\Requests\Catalog;

use App\Models\Category;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function rules(): array
    {
        /** @var Category|string|int|null $category */
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->id : $category;

        return [
            'key' => ['required', 'alpha_dash', 'max:60', Rule::unique('categories', 'key')->ignore($categoryId)],
            'name' => ['required', 'string', 'max:45'],
        ];
    }
}
