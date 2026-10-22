<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;

class PlaceFavoriteController extends Controller
{
    public function toggle(Request $request, Place $place)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], 401);
        }

        $isFavorite = $user->toggleFavorite($place);

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'message' => $isFavorite ? 'Добавлено в избранное' : 'Удалено из избранного'
        ]);
    }

    public function check(Request $request, Place $place)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['is_favorite' => false]);
        }

        $isFavorite = $user->isFavorite($place);

        return response()->json(['is_favorite' => $isFavorite]);
    }
}
