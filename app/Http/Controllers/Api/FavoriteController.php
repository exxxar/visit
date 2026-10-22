<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Place;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Place $place)
    {
        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('place_id', $place->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'success' => true,
                'is_favorite' => false,
                'message' => 'Удалено из избранного'
            ]);
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'place_id' => $place->id,
            ]);
            return response()->json([
                'success' => true,
                'is_favorite' => true,
                'message' => 'Добавлено в избранное'
            ]);
        }
    }

    public function check(Request $request, Place $place)
    {
        $user = $request->user();
        $isFavorite = Favorite::where('user_id', $user->id)
            ->where('place_id', $place->id)
            ->exists();

        return response()->json(['is_favorite' => $isFavorite]);
    }
}
