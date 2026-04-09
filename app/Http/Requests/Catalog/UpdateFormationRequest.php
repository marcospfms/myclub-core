<?php

namespace App\Http\Requests\Catalog;

use App\Models\Formation;
use Illuminate\Validation\Rule;

class UpdateFormationRequest extends StoreFormationRequest
{
    public function rules(): array
    {
        /** @var Formation|string|int|null $formation */
        $formation = $this->route('formation');
        $formationId = $formation instanceof Formation ? $formation->id : $formation;

        return [
            'key' => ['required', 'string', 'max:30', Rule::unique('formations', 'key')->ignore($formationId)],
            'name' => ['required', 'string', 'max:15'],
        ];
    }
}
