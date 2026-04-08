<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label_key' => $this->label_key,
            'description_key' => $this->description_key,
            'icon' => $this->icon,
            'scope' => $this->scope?->value ?? $this->scope,
        ];
    }
}
