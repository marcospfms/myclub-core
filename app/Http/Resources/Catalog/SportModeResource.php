<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SportModeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'label_key' => $this->label_key,
            'description_key' => $this->description_key,
            'icon' => $this->icon,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'formations' => FormationResource::collection($this->whenLoaded('formations')),
            'positions' => PositionResource::collection($this->whenLoaded('positions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
