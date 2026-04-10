<?php

namespace App\Http\Requests\Championship;

class UpdateChampionshipRequest extends StoreChampionshipRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:45'],
            'description' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:150'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_players' => ['sometimes', 'integer', 'min:5', 'max:50'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sport_mode_ids' => ['sometimes', 'array', 'min:1'],
            'sport_mode_ids.*' => ['integer', 'exists:sport_modes,id'],
        ];
    }
}
