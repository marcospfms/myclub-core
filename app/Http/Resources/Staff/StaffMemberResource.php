<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use App\Http\Resources\Shared\UserMinimalResource;
use App\Http\Resources\Catalog\StaffRoleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'user' => UserMinimalResource::make($this->whenLoaded('user')),
            'role' => StaffRoleResource::make($this->whenLoaded('role')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
