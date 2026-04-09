<?php

namespace App\Http\Resources\Player;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isOwner = $request->user()?->id === $this->user_id;
        $isAdmin = $request->user()?->isAdmin() ?? false;

        return [
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'avatar' => $this->user?->avatar,
            'cpf' => $this->when($isOwner || $isAdmin, $this->cpf),
            'rg' => $this->when($isOwner || $isAdmin, $this->rg),
            'birth_date' => $this->birth_date?->toDateString(),
            'phone' => $this->when($isOwner || $isAdmin, $this->phone),
            'is_discoverable' => $this->is_discoverable,
            'history_public' => $this->history_public,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
