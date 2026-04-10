<?php

namespace App\Http\Resources\FriendlyMatch;

use App\Http\Resources\Team\PlayerMembershipResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceHighlightResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'player_membership' => PlayerMembershipResource::make($this->whenLoaded('playerMembership')),
            'goals' => $this->goals,
            'assists' => $this->assists,
            'yellow_cards' => $this->yellow_cards,
            'red_cards' => $this->red_cards,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
