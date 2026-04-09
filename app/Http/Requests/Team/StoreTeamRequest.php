<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:45'],
            'description' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:100'],
            'sport_mode_ids' => ['required', 'array', 'min:1'],
            'sport_mode_ids.*' => ['integer', 'exists:sport_modes,id'],
        ];
    }
}
