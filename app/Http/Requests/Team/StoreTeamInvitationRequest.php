<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'invited_user_id' => ['required', 'integer', 'exists:users,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'message' => ['nullable', 'string', 'max:255'],
        ];
    }
}
