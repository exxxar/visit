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
}
