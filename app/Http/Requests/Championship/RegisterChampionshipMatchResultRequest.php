<?php

namespace App\Http\Requests\Championship;

use Illuminate\Foundation\Http\FormRequest;

class RegisterChampionshipMatchResultRequest extends FormRequest
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
            'home_penalties' => ['nullable', 'integer', 'min:0'],
            'away_penalties' => ['nullable', 'integer', 'min:0'],
            'scheduled_at' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }
}
