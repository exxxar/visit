<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'slug'          => $this->slug,
            'name'          => $this->name,
            'short'         => $this->short_description,
            'rating'        => (float) $this->rating,
            'reviews_count' => $this->reviews_count,
            'price_level'   => $this->price_level,
            'is_featured'   => $this->is_featured,
            'address'       => $this->address,
            'distance_km'   => isset($this->distance) ? round((float) $this->distance, 2) : null,
            'category'      => [
                'name'  => $this->category?->name,
                'icon'  => $this->category?->icon,
                'color' => $this->category?->color,
            ],
            'district' => $this->district?->name,
            'cover'    => $this->photos->firstWhere('is_cover', true)?->path
                    ?? $this->photos->first()?->path,
        ];
    }
}
