<?php

namespace App\Http\Resources\Championship;

use App\Http\Resources\Catalog\CategoryResource;
use App\Http\Resources\Catalog\SportModeResource;
use App\Http\Resources\Shared\UserMinimalResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChampionshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'format' => $this->format?->value ?? $this->format,
            'status' => $this->status?->value ?? $this->status,
            'max_players' => $this->max_players,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'sport_modes' => SportModeResource::collection($this->whenLoaded('sportModes')),
            'teams' => ChampionshipTeamResource::collection($this->whenLoaded('teams')),
            'creator' => UserMinimalResource::make($this->whenLoaded('creator')),
            'awards' => ChampionshipAwardResource::collection($this->whenLoaded('awards')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
