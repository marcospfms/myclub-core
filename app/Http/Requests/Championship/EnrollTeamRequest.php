<?php

namespace App\Http\Requests\Championship;

use Illuminate\Foundation\Http\FormRequest;

class EnrollTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'team_sport_mode_id' => ['required', 'integer', 'exists:team_sport_modes,id'],
        ];
    }
}
