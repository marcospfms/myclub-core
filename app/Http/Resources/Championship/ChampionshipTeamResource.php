<?php

namespace App\Http\Resources\Championship;

use App\Http\Resources\Team\TeamSportModeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChampionshipTeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_sport_mode' => TeamSportModeResource::make($this->whenLoaded('teamSportMode')),
            'players' => ChampionshipTeamPlayerResource::collection($this->whenLoaded('players')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
