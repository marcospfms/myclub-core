<?php

namespace App\Http\Requests\Catalog;

use App\Models\Position;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends StorePositionRequest
{
    public function rules(): array
    {
        /** @var Position|string|int|null $position */
        $position = $this->route('position');
        $positionId = $position instanceof Position ? $position->id : $position;

        return [
            'key' => ['required', 'alpha_dash', 'max:60', Rule::unique('positions', 'key')->ignore($positionId)],
            'label_key' => ['required', 'string', 'max:150'],
            'description_key' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
            'abbreviation' => ['required', 'string', 'size:3'],
        ];
    }
}
