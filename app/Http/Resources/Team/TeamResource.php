<?php

namespace App\Http\Resources\Team;

use Illuminate\Http\Request;
use App\Http\Resources\Shared\UserMinimalResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'badge' => $this->badge,
            'is_active' => $this->is_active,
            'owner' => UserMinimalResource::make($this->whenLoaded('owner')),
            'sport_modes' => TeamSportModeResource::collection($this->whenLoaded('sportModes')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
