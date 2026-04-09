<?php

namespace App\Http\Resources\Team;

use Illuminate\Http\Request;
use App\Http\Resources\Shared\UserMinimalResource;
use App\Http\Resources\Catalog\PositionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_sport_mode' => TeamSportModeResource::make($this->whenLoaded('teamSportMode')),
            'invited_user' => UserMinimalResource::make($this->whenLoaded('invitedUser')),
            'position' => PositionResource::make($this->whenLoaded('position')),
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
