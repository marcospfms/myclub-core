<?php

namespace App\Http\Requests\Championship;

use Illuminate\Foundation\Http\FormRequest;

class SelectPlayersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'player_membership_ids' => ['required', 'array', 'min:1'],
            'player_membership_ids.*' => ['integer', 'exists:player_memberships,id'],
        ];
    }
}
