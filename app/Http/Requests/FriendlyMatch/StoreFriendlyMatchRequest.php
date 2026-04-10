<?php

namespace App\Http\Requests\FriendlyMatch;

use Illuminate\Foundation\Http\FormRequest;

class StoreFriendlyMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'home_team_id' => ['required', 'integer', 'exists:team_sport_modes,id'],
            'away_team_id' => ['required', 'integer', 'exists:team_sport_modes,id', 'different:home_team_id'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
