<?php

namespace App\Http\Requests\Catalog;

use App\Enums\BadgeScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBadgeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'alpha_dash', 'max:60', Rule::unique('badge_types', 'name')],
            'label_key' => ['required', 'string', 'max:150'],
            'description_key' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
            'scope' => ['required', Rule::enum(BadgeScope::class)],
        ];
    }
}
