<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:30', Rule::unique('formations', 'key')],
            'name' => ['required', 'string', 'max:15'],
        ];
    }
}
