<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'label_key' => $this->label_key,
            'description_key' => $this->description_key,
            'icon' => $this->icon,
            'abbreviation' => $this->abbreviation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
