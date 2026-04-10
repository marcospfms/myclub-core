<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\Player\PlayerResource;
use App\Http\Resources\Staff\StaffMemberResource;
use App\Http\Resources\Team\TeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->value,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'player' => PlayerResource::make($this->whenLoaded('player')),
            'staff_member' => StaffMemberResource::make($this->whenLoaded('staffMember')),
            'owned_teams' => TeamResource::collection($this->whenLoaded('ownedTeams')),
            'has_player_profile' => $this->player()->exists(),
            'owns_team' => $this->ownedTeams()->exists(),
        ];
    }
}
