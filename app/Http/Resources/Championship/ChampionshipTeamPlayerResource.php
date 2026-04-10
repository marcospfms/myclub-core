<?php

namespace App\Http\Resources\Championship;

use App\Http\Resources\Team\PlayerMembershipResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChampionshipTeamPlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'player_membership' => PlayerMembershipResource::make($this->whenLoaded('membership')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
