<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'title'  => $this->title,
            'type'   => [
                'value' => $this->type->value,
                'label' => $this->type->label(),
                'icon'  => $this->type->icon(),
            ],
            'date'   => $this->starts_at->format('d M'),
            'time'   => $this->starts_at->format('H:i'),
            'price'  => $this->price,
            'image'  => $this->image,
            'place'  => $this->place?->name,
        ];
    }
}
