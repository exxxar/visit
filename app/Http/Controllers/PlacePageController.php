<?php

namespace App\Http\Controllers;

use App\Enums\ModerationStatus;
use App\Enums\ReviewStatus;
use App\Models\Place;
use Illuminate\Http\Request;

class PlacePageController extends Controller
{
    public function show(Request $request, Place $place)
    {
        // черновик видят только владелец и модераторы
        if ($place->status !== ModerationStatus::Approved) {
            $user = $request->user();
            abort_unless($user && ($user->id === $place->owner_id || $user->can('moderate places')), 404);
        }

        // «записать просмотр» для аналитики
        $place->recordView($request->user()?->id, $request->query('source', 'direct'), $request->ip());

        $place->load([
            'category', 'district', 'photos',
            'reviews' => fn ($q) => $q->where('status', ReviewStatus::Approved)->latest()->with('user'),
        ]);

        return view('place', [
            'place'   => $place,
            'similar' => Place::approved()
                ->where('category_id', $place->category_id)
                ->where('id', '!=', $place->id)
                ->with(['category', 'photos'])
                ->orderByDesc('rating')
                ->take(4)->get(),
        ]);
    }
}
