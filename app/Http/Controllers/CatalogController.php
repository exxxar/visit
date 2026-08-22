<?php

namespace App\Http\Controllers;

use App\Enums\ModerationStatus;
use App\Enums\PlaceStatus;
use App\Models\Category;
use App\Models\District;
use App\Models\Place;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $q      = (string) $request->string('q')->trim();
        $cats   = array_values(array_filter((array) $request->input('categories', [])));
        $dists  = array_values(array_filter((array) $request->input('districts', [])));

        $query = Place::query()
            ->where('status', ModerationStatus::Approved)
            ->with(['category', 'district', 'photos'])
            ->when($q, fn ($b) => $b->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            }))
            ->when($cats, fn ($b) => $b->whereHas('category', fn ($w) => $w->whereIn('slug', $cats)))
            ->when($dists, fn ($b) => $b->whereHas('district', fn ($w) => $w->whereIn('slug', $dists)))
            ->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->orderByDesc('views_count');

        $places = $query->paginate(24)->withQueryString();

        return view('catalog', [
            'places'        => $places,
            'q'             => $q,
            'selectedCats'  => $cats,
            'selectedDists' => $dists,
            'categories'    => Category::whereNull('parent_id')->orderBy('sort')->get(),
            'districts'     => District::orderBy('sort')->get(),
        ]);
    }
}
