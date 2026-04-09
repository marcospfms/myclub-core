<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSportModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'alpha_dash', 'max:60', Rule::unique('sport_modes', 'key')],
            'label_key' => ['required', 'string', 'max:150'],
            'description_key' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'formation_ids' => ['sometimes', 'array'],
            'formation_ids.*' => ['integer', 'exists:formations,id'],
            'position_ids' => ['sometimes', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
        ];
    }
}
