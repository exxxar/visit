<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\PlaceReview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TouristDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'favorites_count' => Favorite::where('user_id', $user->id)->count(),
            'reviews_count' => PlaceReview::where('user_id', $user->id)->count(),
            'approved_reviews' => PlaceReview::where('user_id', $user->id)
                ->where('status', 'approved')->count(),
        ];

        $recentFavorites = Favorite::where('user_id', $user->id)
            ->with('place:id,name,slug,cover_url,rating')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($f) => $f->place);

        $recentReviews = PlaceReview::where('user_id', $user->id)
            ->with('place:id,name,slug')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Account/Tourist/Dashboard', [
            'stats' => $stats,
            'recentFavorites' => $recentFavorites,
            'recentReviews' => $recentReviews,
        ]);
    }
}
