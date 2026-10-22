<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoritesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $favorites = Favorite::where('user_id', $user->id)
            ->with('place:id,name,slug,cover_url,rating,reviews_count,address,category_id,district_id')
            ->with('place.category:id,name,icon')
            ->with('place.district:id,name')
            ->latest()
            ->paginate(12);

        return Inertia::render('Account/Tourist/Favorites', [
            'favorites' => $favorites,
        ]);
    }
}
