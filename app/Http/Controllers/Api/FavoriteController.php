<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Place $place)
    {
        $user = $request->user();
        $attached = $user->favorites()->toggle($place->id);

        return response()->json([
            'is_favorite' => ! empty($attached['attached']),
        ]);
    }
}
