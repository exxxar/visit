<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlacementSlot;
use App\Enums\PlacementStatus;
use App\Http\Controllers\Controller;
use App\Models\FeaturedPlacement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PlacementController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Promo/Index', [
            'placements' => FeaturedPlacement::with('place')->orderByDesc('starts_at')->get(),
            'slots'      => PlacementSlot::options(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'place_id'   => ['required', 'exists:places,id'],
            'slot'       => ['required', Rule::enum(PlacementSlot::class)],
            'starts_at'  => ['required', 'date'],
            'ends_at'    => ['required', 'date', 'after_or_equal:starts_at'],
            'price'      => ['nullable', 'numeric', 'min:0'],
        ]);

        FeaturedPlacement::create($data + ['status' => PlacementStatus::Scheduled]);

        return back()->with('success', 'Размещение запланировано');
    }

    public function update(Request $request, FeaturedPlacement $placement)
    {
        $data = $request->validate(['status' => ['required', Rule::enum(PlacementStatus::class)]]);

        $placement->update($data);

        return back()->with('success', 'Статус размещения обновлён');
    }

    public function destroy(FeaturedPlacement $placement)
    {
        $placement->delete();

        return back()->with('success', 'Размещение удалено');
    }
}
