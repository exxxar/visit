<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'slug'     => $this->slug,
            'title'    => $this->title,
            'tag'      => $this->tag,
            'excerpt'  => $this->excerpt,
            'cover'    => $this->cover,
            'published'=> $this->published_at?->toDateString(),
            'places'   => $this->whenLoaded('places', fn () =>
            $this->places->map(fn ($p) => ['slug' => $p->slug, 'name' => $p->name])
            ),
        ];
    }
}
