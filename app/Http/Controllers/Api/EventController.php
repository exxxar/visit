<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EventResource;
use App\Enums\EventType;
use App\Enums\ModerationStatus;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => ['nullable', Rule::enum(EventType::class)],
        ]);

        $events = Event::query()
            ->where('status', ModerationStatus::Approved)
            ->upcoming()
            ->with('place')
            ->when($request->enum('type'), fn ($q, $type) => $q->where('type', $type))
            ->orderBy('starts_at')
            ->take(30)
            ->get();

        return EventResource::collection($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'type'        => ['required', 'string', 'in:' . implode(',', array_column(EventType::cases(), 'value'))],
            'date'        => ['required', 'date', 'after_or_equal:today'],
            'time'        => ['required', 'date_format:H:i'],
            'place_id'    => ['nullable', 'integer', 'exists:places,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'contact'     => ['nullable', 'string', 'max:200'],
        ]);

        // Формируем datetime
        $startsAt = $validated['date'] . ' ' . $validated['time'] . ':00';

        // Загружаем изображение
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        // Создаём событие
        $event = Event::create([
            'title'       => $validated['title'],
            'slug'        => Str::slug($validated['title']) . '-' . Str::random(4),
            'type'        => EventType::from($validated['type']),
            'starts_at'   => $startsAt,
            'place_id'    => $validated['place_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'image'       => $imagePath,
            'status'      => ModerationStatus::OnModeration,
            'meta'        => [
                'contact' => $validated['contact'] ?? null,
                'submitted_by' => $request->ip(),
            ],
        ]);

        return response()->json([
            'message' => 'Событие отправлено на модерацию',
            'event_id' => $event->id,
        ], 201);
    }
}
