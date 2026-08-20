<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PlaceDetailResource extends PlaceResource
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request) + [
                'description'   => $this->description,
                'lat'           => (float) $this->lat,
                'lng'           => (float) $this->lng,
                'phone'         => $this->phone,
                'email'         => $this->email,
                'site'          => $this->site,
                'socials'       => $this->socials,
                'working_hours' => $this->working_hours,
                'views_count'   => $this->views_count,
                'photos'        => $this->photos->map->path->values(),
                'reviews'       => ReviewResource::collection(
                    $this->reviews->where('status.value', 'approved')->take(5)
                ),
            ];
    }
}
