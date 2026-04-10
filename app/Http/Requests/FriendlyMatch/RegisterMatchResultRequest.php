<?php

namespace App\Http\Requests\FriendlyMatch;

use Illuminate\Foundation\Http\FormRequest;

class RegisterMatchResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'home_goals' => ['required', 'integer', 'min:0'],
            'away_goals' => ['required', 'integer', 'min:0'],
            'home_notes' => ['nullable', 'string'],
            'away_notes' => ['nullable', 'string'],
        ];
    }
}
