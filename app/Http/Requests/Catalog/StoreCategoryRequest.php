<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'alpha_dash', 'max:60', Rule::unique('categories', 'key')],
            'name' => ['required', 'string', 'max:45'],
        ];
    }
}
