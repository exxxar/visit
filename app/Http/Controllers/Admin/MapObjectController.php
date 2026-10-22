<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapLayer;
use App\Models\MapObject;
use App\Models\Place;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MapObjectController extends Controller
{
    public function index(Request $request)
    {
        $query = MapObject::with(['layer', 'place:id,name']);
        if ($request->layer_id) $query->where('layer_id', $request->layer_id);

        return Inertia::render('Admin/Map/Objects', [
            'objects' => $query->orderBy('sort_order')->get(),
            'layers' => MapLayer::all(),
            'places' => Place::select('id', 'name')->get(), // Для выпадающего списка
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'layer_id'    => 'required|exists:map_layers,id',
            'mode'        => 'required|in:custom,place',
            'place_id'    => 'nullable|exists:places,id',
            'title'       => 'required_unless:mode,place|string|max:200',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'lat'         => 'required|numeric',
            'lng'         => 'required|numeric',
            'x'           => 'nullable|numeric',
            'y'           => 'nullable|numeric',
            'is_active'   => 'boolean',
        ]);

        // Если режим 'place', очищаем кастомные поля, чтобы не мусорить в БД
        if ($data['mode'] === 'place') {
            $data['title'] = null;
            $data['description'] = null;
            $data['image'] = null;
            $data['x'] = null;
            $data['y'] = null;
        }

        MapObject::create($data);
        return back()->with('success', 'Объект успешно добавлен на карту');
    }

    public function editor()
    {
        $layers = MapLayer::orderBy('sort_order')->get();

        // Загружаем объекты вместе с данными слоя и (опционально) заведения
        $objects = MapObject::with(['layer', 'place:id,name'])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($obj) {
                return [
                    'id' => $obj->id,
                    'layer_id' => $obj->layer_id,
                    'layer_color' => $obj->layer->color,
                    'layer_icon' => $obj->layer->icon,
                    'name' => $obj->place ? $obj->place->name : $obj->title,
                    'lat' => (float) $obj->lat,
                    'lng' => (float) $obj->lng,
                    'x' => $obj->x,
                    'y' => $obj->y,
                    'is_place' => (bool) $obj->place_id,
                ];
            });

        return Inertia::render('Admin/Map/Editor', [
            'layers' => $layers,
            'objects' => $objects,
            'places' => \App\Models\Place::select('id', 'name')->get(),
        ]);
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'objects' => 'required|array',
            'objects.*.id' => 'required|exists:map_objects,id',
            'objects.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($data['objects'] as $item) {
            MapObject::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return back()->with('success', 'Порядок объектов обновлён');
    }

    public function destroy(MapObject $object)
    {
        $object->delete();
        return back()->with('success', 'Объект удален');
    }
}
