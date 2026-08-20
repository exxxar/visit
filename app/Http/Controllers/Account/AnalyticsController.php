<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function show(Request $request, Place $place)
    {
        abort_unless($request->user()->id === $place->owner_id
            || $request->user()->hasRole('admin'), 403);

        return Inertia::render('Account/Analytics', [
            'place'   => $place->only(['id', 'name', 'views_count', 'rating', 'reviews_count']),
            'myPlaces'=> $request->user()->ownedPlaces()->get(['id', 'name']),
            'series'  => $place->stats()->where('date', '>=', now()->subDays(30))->orderBy('date')->get(),
            'sources' => $place->views()->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('source, COUNT(*) as c')->groupBy('source')->orderByDesc('c')->get(),
        ]);
    }
}
