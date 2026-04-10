<?php

namespace App\Http\Resources\Championship;

use App\Http\Resources\Player\PlayerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChampionshipAwardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'award_type' => $this->award_type?->value ?? $this->award_type,
            'value' => $this->value,
            'player' => PlayerResource::make($this->whenLoaded('player')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
