<?php

namespace App\Http\Requests\Catalog;

use App\Models\BadgeType;
use Illuminate\Validation\Rule;

class UpdateBadgeTypeRequest extends StoreBadgeTypeRequest
{
    public function rules(): array
    {
        /** @var BadgeType|string|int|null $badgeType */
        $badgeType = $this->route('badge_type');
        $badgeTypeId = $badgeType instanceof BadgeType ? $badgeType->id : $badgeType;

        return [
            'name' => ['required', 'alpha_dash', 'max:60', Rule::unique('badge_types', 'name')->ignore($badgeTypeId)],
            'label_key' => ['required', 'string', 'max:150'],
            'description_key' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
            'scope' => parent::rules()['scope'],
        ];
    }
}
