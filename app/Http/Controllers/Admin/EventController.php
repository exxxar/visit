<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventType;
use App\Enums\ModerationStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::query()
            ->with(['place:id,name'])          // ← убрали 'type' (это не связь)
            ->when($request->q, fn ($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->type, fn ($q, $s) => $q->where('type', $s))
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Events/Index', [
            'events' => $events,
            'types'  => EventType::cases(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Events/Edit', [
            'event'  => null,
            'types'  => EventType::cases(),
            'places' => Place::approved()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'type'        => ['required', Rule::enum(EventType::class)],
            'starts_at'   => ['required', 'date'],
            'ends_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
            'place_id'    => ['nullable', 'exists:places,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price'       => ['nullable', 'string', 'max:100'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status'      => ['required', Rule::enum(ModerationStatus::class)],
        ]);

        $slug = Str::slug($data['title']);
        while (Event::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(3));
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        Event::create([
            'title'       => $data['title'],
            'slug'        => $slug,
            'type'        => EventType::from($data['type']),
            'starts_at'   => $data['starts_at'],
            'ends_at'     => $data['ends_at'] ?? null,
            'place_id'    => $data['place_id'] ?? null,
            'author_id'   => auth()->id(),
            'description' => $data['description'] ?? null,
            'price'       => $data['price'] ?? null,
            'image'       => $imagePath,
            'status'      => ModerationStatus::from($data['status']),
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', 'Событие создано');
    }

    public function edit(Event $event)
    {
        return Inertia::render('Admin/Events/Edit', [
            'event'  => $event->load('place:id,name'),
            'types'  => EventType::cases(),
            'places' => Place::approved()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'type'        => ['required', Rule::enum(EventType::class)],
            'starts_at'   => ['required', 'date'],
            'ends_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
            'place_id'    => ['nullable', 'exists:places,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price'       => ['nullable', 'string', 'max:100'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status'      => ['required', Rule::enum(ModerationStatus::class)],
        ]);

        $imagePath = $event->image;
        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $imagePath = $request->file('image')->store('events', 'public');
        }

        $event->update([
            'title'       => $data['title'],
            'type'        => EventType::from($data['type']),
            'starts_at'   => $data['starts_at'],
            'ends_at'     => $data['ends_at'] ?? null,
            'place_id'    => $data['place_id'] ?? null,
            'description' => $data['description'] ?? null,
            'price'       => $data['price'] ?? null,
            'image'       => $imagePath,
            'status'      => ModerationStatus::from($data['status']),
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', 'Событие обновлено');
    }

    public function destroy(Event $event)
    {
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        $event->delete();

        return back()->with('success', 'Событие удалено');
    }
}
