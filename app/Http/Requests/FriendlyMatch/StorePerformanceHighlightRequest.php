<?php

namespace App\Http\Requests\FriendlyMatch;

use Illuminate\Foundation\Http\FormRequest;

class StorePerformanceHighlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'highlights' => ['required', 'array', 'min:1'],
            'highlights.*.player_membership_id' => ['required', 'integer', 'exists:player_memberships,id'],
            'highlights.*.goals' => ['sometimes', 'integer', 'min:0'],
            'highlights.*.assists' => ['sometimes', 'integer', 'min:0'],
            'highlights.*.yellow_cards' => ['sometimes', 'integer', 'min:0'],
            'highlights.*.red_cards' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
