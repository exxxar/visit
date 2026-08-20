<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlaceIndexRequest;
use App\Http\Resources\PlaceDetailResource;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index(PlaceIndexRequest $request)
    {
        $q = Place::query()
            ->approved()
            ->with(['category', 'district', 'photos'])
            ->when($request->district, fn ($q) => $q->whereRelation('district', 'slug', $request->district))
            ->when($request->category, fn ($q) => $q->whereRelation('category', 'slug', $request->category))
            ->when($request->q, fn ($q) => $q->whereFullText(
                ['name', 'address', 'short_description'], $request->q
            ))
            ->when($request->validated('near'), fn ($q) => $q->near(
                $request->near['lat'], $request->near['lng'], $request->near['km'] ?? 3
            ));

        $q = match ($request->sort ?? 'rating') {
            'new'   => $q->orderByDesc('created_at'),
            'name'  => $q->orderBy('name'),
            'views' => $q->orderByDesc('views_count'),
            default => $q->orderByDesc('rating'),
        };

        return PlaceResource::collection($q->paginate($request->integer('per_page', 12)));
    }

    public function show(string $slug, Request $request)
    {
        $place = Place::approved()
            ->where('slug', $slug)
            ->with(['category', 'district', 'photos', 'reviews' => fn ($r) => $r->approved()->latest()->take(5)])
            ->firstOrFail();

        $place->recordView($request->user()?->id, $request->query('source'), $request->ip());

        return new PlaceDetailResource($place);
    }
}
