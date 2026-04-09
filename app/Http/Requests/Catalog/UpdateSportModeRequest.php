<?php

namespace App\Http\Requests\Catalog;

use App\Models\SportMode;
use Illuminate\Validation\Rule;

class UpdateSportModeRequest extends StoreSportModeRequest
{
    public function rules(): array
    {
        /** @var SportMode|string|int|null $sportMode */
        $sportMode = $this->route('sport_mode');
        $sportModeId = $sportMode instanceof SportMode ? $sportMode->id : $sportMode;

        return [
            ...parent::rules(),
            'key' => ['required', 'alpha_dash', 'max:60', Rule::unique('sport_modes', 'key')->ignore($sportModeId)],
        ];
    }
}
