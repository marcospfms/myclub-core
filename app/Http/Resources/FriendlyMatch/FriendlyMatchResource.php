<?php

namespace App\Http\Resources\FriendlyMatch;

use App\Http\Resources\Shared\UserMinimalResource;
use App\Http\Resources\Team\TeamSportModeResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendlyMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'home_team' => TeamSportModeResource::make($this->whenLoaded('homeTeam')),
            'away_team' => TeamSportModeResource::make($this->whenLoaded('awayTeam')),
            'scheduled_at' => $this->scheduled_at,
            'location' => $this->location,
            'confirmation' => $this->confirmation?->value,
            'invite_expires_at' => $this->invite_expires_at,
            'match_status' => $this->match_status?->value,
            'home_goals' => $this->home_goals,
            'away_goals' => $this->away_goals,
            'home_notes' => $this->when($this->canViewHomeNotes($request), $this->home_notes),
            'away_notes' => $this->when($this->canViewAwayNotes($request), $this->away_notes),
            'is_public' => $this->is_public,
            'result_status' => $this->result_status?->value,
            'result_registered_by' => UserMinimalResource::make($this->whenLoaded('resultRegisteredBy')),
            'highlights' => PerformanceHighlightResource::collection($this->whenLoaded('highlights')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function canViewHomeNotes(Request $request): bool
    {
        return $this->canViewTeamNotes($request, 'homeTeam');
    }

    private function canViewAwayNotes(Request $request): bool
    {
        return $this->canViewTeamNotes($request, 'awayTeam');
    }

    private function canViewTeamNotes(Request $request, string $relation): bool
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (! $this->relationLoaded($relation) || ! $this->{$relation}?->relationLoaded('team')) {
            return false;
        }

        return $this->{$relation}->team?->owner_id === $user->id;
    }
}
