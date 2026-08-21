<?php

namespace App\Http\Controllers;

use App\Enums\EventType;
use App\Enums\ModerationStatus;
use App\Models\Event;
use Illuminate\Http\Request;

class EventPageController extends Controller
{
    public function show(Request $request, Event $event)
    {
        if ($event->status !== ModerationStatus::Approved) {
            $user = $request->user();
            abort_unless($user && ($user->id === $event->author_id
                    || ($event->place && $user->id === $event->place->owner_id)
                    || $user->hasRole('admin')), 404);
        }

        $event->load(['place' => fn ($q) => $q->with(['category', 'district', 'photos'])]);

        return view('event', [
            'event'   => $event,
            'related' => Event::where('status', ModerationStatus::Approved)
                ->where('id', '!=', $event->id)
                ->where('starts_at', '>=', now())
                ->with('place')
                ->orderBy('starts_at')
                ->take(3)->get(),
        ]);
    }
}
