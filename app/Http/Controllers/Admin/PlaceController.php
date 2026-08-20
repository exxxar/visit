<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPlaceRequest;
use App\Models\Place;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $places = Place::query()
            ->with(['category', 'district', 'owner'])
            ->when($request->q, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->district_id, fn ($q, $d) => $q->where('district_id', $d))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Places/Index', ['places' => $places]);
    }

    public function edit(Place $place)
    {
        return Inertia::render('Admin/Places/Edit', [
            'place'      => $place->load(['category', 'district', 'photos', 'owner']),
            'categories' => \App\Models\Category::orderBy('sort')->get(['id', 'name', 'parent_id']),
            'districts'  => \App\Models\District::orderBy('sort')->get(['id', 'name']),
        ]);
    }

    public function update(AdminPlaceRequest $request, Place $place)
    {
        $place->update($request->validated() + ['is_featured' => $request->boolean('is_featured')]);

        return back()->with('success', 'Карточка сохранена');
    }

    public function toggleFeatured(Place $place)
    {
        $place->update(['is_featured' => ! $place->is_featured]);

        return back()->with('success', $place->is_featured ? 'Спецразмещение включено' : 'Спецразмещение выключено');
    }

    public function destroy(Place $place)
    {
        $place->delete();

        return back()->with('success', 'Заведение скрыто с сайта');
    }

    public function analytics(Place $place)
    {
        return Inertia::render('Admin/Places/Analytics', [
            'place'   => $place->only(['id', 'name', 'views_count', 'rating', 'reviews_count']),
            'series'  => $place->stats()
                ->where('date', '>=', now()->subDays(30))->orderBy('date')->get(),
            'sources' => $place->views()
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('source, COUNT(*) as c')
                ->groupBy('source')->orderByDesc('c')->get(),
        ]);
    }
}
