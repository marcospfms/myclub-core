<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'alpha_dash', 'max:60', Rule::unique('staff_roles', 'name')],
            'label_key' => ['required', 'string', 'max:150'],
            'description_key' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
        ];
    }
}
