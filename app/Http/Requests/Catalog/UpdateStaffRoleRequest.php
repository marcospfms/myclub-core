<?php

namespace App\Http\Requests\Catalog;

use App\Models\StaffRole;
use Illuminate\Validation\Rule;

class UpdateStaffRoleRequest extends StoreStaffRoleRequest
{
    public function rules(): array
    {
        /** @var StaffRole|string|int|null $staffRole */
        $staffRole = $this->route('staff_role');
        $staffRoleId = $staffRole instanceof StaffRole ? $staffRole->id : $staffRole;

        return [
            'name' => ['required', 'alpha_dash', 'max:60', Rule::unique('staff_roles', 'name')->ignore($staffRoleId)],
            'label_key' => ['required', 'string', 'max:150'],
            'description_key' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
        ];
    }
}
