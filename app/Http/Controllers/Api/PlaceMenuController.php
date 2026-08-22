<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PlaceMenuController extends Controller
{
    public function show(Request $request, Place $place)
    {
        // Проверка, что заведение импортировано из mypwa
        if ($place->external_source !== 'mypwa.ru' || !$place->external_id) {
            return response()->json([
                'error' => 'Меню недоступно для этого заведения',
            ], 404);
        }

        // Кэшируем на 15 минут, чтобы не долбить API
        $cacheKey = "mypwa_menu_{$place->external_id}";

        $menu = Cache::remember($cacheKey, 900, function () use ($place) {
            try {
                $response = Http::timeout(10)
                    ->withToken(config('services.mypwa.token'))
                    ->get(config('services.mypwa.api_url') . "/tenants/{$place->external_id}/products");

                if (!$response->ok()) {
                    Log::warning('[MYPWA] Failed to fetch menu', [
                        'uuid' => $place->external_id,
                        'status' => $response->status(),
                    ]);
                    return [];
                }

                return $response->json('data', []);
            } catch (\Throwable $e) {
                Log::error('[MYPWA] Menu fetch error', [
                    'uuid' => $place->external_id,
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });

        return response()->json([
            'data' => $menu,
            'count' => count($menu),
        ]);
    }
}
