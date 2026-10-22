<?php

namespace App\Http\Controllers\Account;

use App\Enums\ModerationStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\District;
use App\Models\Place;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlaceController extends Controller
{
    public function edit(Request $request, Place $place)
    {
        $this->authorize('update', $place);

        return Inertia::render('Account/Places/Edit', [
            'place'      => $place->load(['category', 'district', 'photos']),
            'categories' => Category::orderBy('sort')->get(['id', 'name', 'parent_id']),
            'districts'  => District::orderBy('sort')->get(['id', 'name']),
        ]);
    }


    public function promote(Request $request, Place $place)
    {
        // Проверка что пользователь — владелец
        abort_unless($place->owner_id === $request->user()->id, 403);

        $data = $request->validate([
            'service'      => ['required', 'in:smm,targeting,content,video,influencer,complex'],
            'requirements' => ['nullable', 'string', 'max:1000'],
        ]);

        $services = \App\Models\PromotionRequest::SERVICES;
        $svc = $services[$data['service']] ?? null;

        abort_unless($svc, 422, 'Неизвестный тариф');

        $promotion = \App\Models\PromotionRequest::create([
            'user_id'      => $request->user()->id,
            'place_id'     => $place->id,
            'service'      => $data['service'],
            'title'        => $svc['label'],
            'amount'       => $svc['price'],
            'status'       => 'new',
            'requirements' => $data['requirements'] ?? null,
            'meta'         => [
                'place_name' => $place->name,
                'category'   => $place->category?->name,
                'district'   => $place->district?->name,
                'address'    => $place->address,
                'phone'      => $place->phone,
            ],
        ]);

        return back()->with('success', "Заявка «{$svc['label']}» создана. Рекламный партнёр скоро свяжется с вами.");
    }

    public function update(Request $request, Place $place)
    {
        $this->authorize('update', $place);

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:120'],
            'category_id'       => ['required', 'exists:categories,id'],
            'district_id'       => ['required', 'exists:districts,id'],
            'address'           => ['required', 'string', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'site'              => ['nullable', 'url'],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description'       => ['nullable', 'string', 'max:5000'],
            'price_level'       => ['required', 'integer', 'between:1,3'],
            'socials'           => ['nullable', 'array'],
        ]);

        $place->update($data);

        // владелец поменял контент → карточка уходит на ре-модерацию
        $place->update(['status' => ModerationStatus::OnModeration]);

        return back()->with('success', 'Сохранено. Карточка отправлена на ре-модерацию.');
    }

    public function addPhoto(Request $request, Place $place)
    {
        $this->authorize('update', $place);

        $request->validate(['image' => ['required', 'image', 'max:5120']]);

        $path = $request->file('image')->store('uploads/places', 'public');

        $place->photos()->create([
            'path' => '/storage/' . $path,
            'is_cover' => $place->photos()->count() === 0,
            'sort' => $place->photos()->count(),
        ]);

        return back()->with('success', 'Фото добавлено');
    }
}
