<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'author'  => $this->user?->name ?? $this->author_name,
            'rating'  => $this->rating,
            'text'    => $this->text,
            'created' => $this->created_at?->diffForHumans(),
        ];
    }
}
