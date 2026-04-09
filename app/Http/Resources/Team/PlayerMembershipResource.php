<?php

namespace App\Http\Resources\Team;

use Illuminate\Http\Request;
use App\Http\Resources\Player\PlayerResource;
use App\Http\Resources\Catalog\PositionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerMembershipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'player' => PlayerResource::make($this->whenLoaded('player')),
            'position' => PositionResource::make($this->whenLoaded('position')),
            'is_starter' => $this->is_starter,
            'left_at' => $this->left_at,
            'joined_at' => $this->created_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
