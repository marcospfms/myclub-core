<?php

namespace App\Http\Requests\Player;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StorePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cpf' => ['nullable', 'string', 'size:11', Rule::unique('players', 'cpf')->ignore($this->user()?->id, 'user_id')],
            'rg' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:15'],
            'is_discoverable' => ['sometimes', 'boolean'],
            'history_public' => ['sometimes', 'boolean'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:60'],
            'country' => ['nullable', 'string', 'size:2'],
        ];
    }
}
