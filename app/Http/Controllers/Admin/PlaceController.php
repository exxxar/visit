<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ModerationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPlaceRequest;
use App\Http\Requests\Admin\StoreAdminPlaceRequest;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $places = Place::query()
            ->with(['category', 'district', 'owner'])
            ->when($request->q, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->district_id, fn ($q, $d) => $q->where('district_id', $d))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Places/Index', ['places' => $places]);
    }

    public function edit(Place $place)
    {
        return Inertia::render('Admin/Places/Edit', [
            'place'      => $place->load(['category', 'district', 'photos', 'owner']),
            'categories' => \App\Models\Category::orderBy('sort')->get(['id', 'name', 'parent_id']),
            'districts'  => \App\Models\District::orderBy('sort')->get(['id', 'name']),
        ]);
    }


    public function update(AdminPlaceRequest $request, Place $place)
    {
        $data = $request->validated();

        $place->update([
            'name'              => $data['name'],
            'category_id'       => $data['category_id'],
            'district_id'       => $data['district_id'],
            'address'           => $data['address'],
            'lat'               => $data['lat'],
            'lng'               => $data['lng'],
            'phone'             => $data['phone'] ?? null,
            'email'             => $data['email'] ?? null,
            'site'              => $data['site'] ?? null,
            'price_level'       => $data['price_level'],
            'short_description' => $data['short_description'] ?? null,
            'description'       => $data['description'] ?? null,
            'working_hours'     => $data['working_hours'] ?? null,
            'socials'           => $data['socials'] ?? null,
            'is_featured'       => $request->boolean('is_featured'),
            'status'            => $data['status'] ?? $place->status,
        ]);

        // 2. Удаляем выбранные фото
        if ($deleteIds = $request->input('delete_photos', [])) {
            $photos = $place->photos()->whereIn('id', (array) $deleteIds)->get();
            foreach ($photos as $ph) {
                // Убираем /storage/ для Storage::delete()
                $rawPath = str_replace('/storage/', '', $ph->path);
                Storage::disk('public')->delete($rawPath);
                $ph->delete();
            }
        }

        // 3. Добавляем новые фото
        $newFiles = $request->file('photos', []);
        $hasAnyPhoto = $place->photos()->exists();
        $maxSort = $place->photos()->max('sort') ?? 0;

        foreach ($newFiles as $i => $file) {
            $relativePath = $file->store('places/' . $place->id, 'public');
            $fullPath = '/storage/' . $relativePath;  // ← добавляем /storage/

            $place->photos()->create([
                'path'     => $fullPath,
                'is_cover' => !$hasAnyPhoto && $i === 0,
                'sort'     => $maxSort + $i + 1,
            ]);
            $hasAnyPhoto = true;
        }

        // 4. Обновляем обложку
        $coverId = $request->input('cover_photo_id');
        if ($coverId && $place->photos()->where('id', $coverId)->exists()) {
            $place->photos()->update(['is_cover' => false]);
            $place->photos()->where('id', $coverId)->update(['is_cover' => true]);
        } elseif (!$place->photos()->where('is_cover', true)->exists()) {
            $first = $place->photos()->orderBy('sort')->first();
            if ($first) $first->update(['is_cover' => true]);
        }

        return back()->with('success', 'Карточка сохранена');
    }

    public function toggleFeatured(Place $place)
    {
        $place->update(['is_featured' => ! $place->is_featured]);

        return back()->with('success', $place->is_featured ? 'Спецразмещение включено' : 'Спецразмещение выключено');
    }

    public function updateRating(Request $request, Place $place)
    {
        $data = $request->validate([
            'rating'        => ['required', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['required', 'integer', 'min:0'],
        ]);

        $place->update([
            'rating'        => round((float) $data['rating'], 2),
            'reviews_count' => (int) $data['reviews_count'],
        ]);

        return back()->with('success', "Рейтинг «{$place->name}» обновлён");
    }

    public function recalculateRating(Place $place)
    {
        $place->recalculateRating();

        return back()->with('success', "Рейтинг пересчитан из одобренных отзывов: ★{$place->rating} ({$place->reviews_count} отзывов)");
    }

    public function destroy(Place $place)
    {
        $place->delete();

        return back()->with('success', 'Заведение скрыто с сайта');
    }

    public function analytics(Place $place)
    {
        return Inertia::render('Admin/Places/Analytics', [
            'place'   => $place->only(['id', 'name', 'views_count', 'rating', 'reviews_count']),
            'series'  => $place->stats()
                ->where('date', '>=', now()->subDays(30))->orderBy('date')->get(),
            'sources' => $place->views()
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('source, COUNT(*) as c')
                ->groupBy('source')->orderByDesc('c')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Places/Create', [
            'categories' => \App\Models\Category::orderBy('sort')->get(['id', 'name', 'parent_id']),
            'districts'  => \App\Models\District::orderBy('sort')->get(['id', 'name']),
            'owners'     => User::role('business')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(StoreAdminPlaceRequest $request)
    {
        $data = $request->validated();

        $slug = Str::slug($data['name']);
        while (Place::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(3));
        }

        $place = Place::create([
            'name'              => $data['name'],
            'slug'              => $slug,
            'category_id'       => $data['category_id'],
            'district_id'       => $data['district_id'],
            'address'           => $data['address'],
            'phone'             => $data['phone'] ?? null,
            'email'             => $data['email'] ?? null,
            'site'              => $data['site'] ?? null,
            'price_level'       => $data['price_level'],
            'short_description' => $data['short_description'] ?? null,
            'description'       => $data['description'] ?? null,
            'lat'               => $data['lat'],
            'lng'               => $data['lng'],
            'working_hours'     => $data['working_hours'] ?? null,
            'socials'           => $data['socials'] ?? null,
            'is_featured'       => $request->boolean('is_featured'),
            'status'            => ModerationStatus::Approved,
            'owner_id'          => isset($data['owner_email'])
                ? User::where('email', $data['owner_email'])->value('id')
                : null,
        ]);

        // фото: первое — обложка
        foreach ($request->file('photos', []) as $i => $file) {
            $relativePath = $file->store('places/' . $place->id, 'public');
            $fullPath = '/storage/' . $relativePath;  // ← добавляем /storage/

            $place->photos()->create([
                'path'     => $fullPath,
                'is_cover' => $i === 0,
            ]);
        }

        return redirect()->route('admin.places.index')
            ->with('success', "Заведение «{$place->name}» создано и опубликовано");
    }
}
