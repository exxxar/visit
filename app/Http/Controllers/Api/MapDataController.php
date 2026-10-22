<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MapLayer;

class MapDataController extends Controller
{
    public function __invoke()
    {
        $layers = MapLayer::where('is_active', true)
            ->with(['objects' => function ($q) {
                $q->where('is_active', true)
                    ->with('place:id,name,slug,short_description,cover');
            }])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($layer) {
                return [
                    'id' => $layer->id,
                    'name' => $layer->name,
                    'slug' => $layer->slug,
                    'color' => $layer->color,
                    'icon' => $layer->icon,
                    'objects' => $layer->objects->map(function ($obj) {
                        return [
                            'id' => $obj->id,
                            'place_id' => $obj->place_id,
                            'slug' => $obj->place?->slug, // Если это заведение, даем ссылку
                            'name' => $obj->display_name,
                            'description' => $obj->display_description,
                            'image' => $obj->display_image,
                            'lat' => (float) $obj->lat,
                            'lng' => (float) $obj->lng,
                        ];
                    }),
                ];
            });

        return response()->json(['layers' => $layers]);
    }
}
