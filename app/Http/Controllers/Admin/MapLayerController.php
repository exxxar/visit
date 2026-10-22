<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapLayer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MapLayerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Map/Layers', [
            // Загружаем слой сразу с его объектами и данными заведения (если есть)
            'layers' => MapLayer::orderBy('sort_order')
                ->with(['objects' => function ($query) {
                    $query->with('place:id,name')
                        ->orderBy('sort_order');
                }])
                ->withCount('objects')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'color' => 'required|string|max:20',
            'icon' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);
        $data['slug'] = Str::slug($data['name']);
        MapLayer::create($data);
        return back()->with('success', 'Слой создан');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'layers' => 'required|array',
            'layers.*.id' => 'required|exists:map_layers,id',
            'layers.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($data['layers'] as $item) {
            MapLayer::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return back()->with('success', 'Порядок слоёв обновлён');
    }

    public function destroy(MapLayer $layer)
    {
        $layer->delete();
        return back()->with('success', 'Слой удален');
    }
}
