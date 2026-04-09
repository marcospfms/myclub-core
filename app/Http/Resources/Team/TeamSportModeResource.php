<?php

namespace App\Http\Resources\Team;

use Illuminate\Http\Request;
use App\Http\Resources\Catalog\SportModeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamSportModeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sport_mode' => SportModeResource::make($this->whenLoaded('sportMode')),
            'member_count' => $this->relationLoaded('activeMemberships')
                ? $this->activeMemberships->count()
                : $this->activeMemberships()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
