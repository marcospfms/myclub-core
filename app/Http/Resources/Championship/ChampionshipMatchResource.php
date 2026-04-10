<?php

namespace App\Http\Resources\Championship;

use App\Http\Resources\Team\TeamSportModeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChampionshipMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'round' => $this->whenLoaded('round', fn (): array => [
                'id' => $this->round->id,
                'name' => $this->round->name,
                'round_number' => $this->round->round_number,
            ]),
            'home_team' => TeamSportModeResource::make($this->whenLoaded('homeTeam')),
            'away_team' => TeamSportModeResource::make($this->whenLoaded('awayTeam')),
            'scheduled_at' => $this->scheduled_at,
            'location' => $this->location,
            'match_status' => $this->match_status?->value ?? $this->match_status,
            'home_goals' => $this->home_goals,
            'away_goals' => $this->away_goals,
            'home_penalties' => $this->home_penalties,
            'away_penalties' => $this->away_penalties,
            'leg' => $this->leg,
            'highlights' => ChampionshipMatchHighlightResource::collection($this->whenLoaded('highlights')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
