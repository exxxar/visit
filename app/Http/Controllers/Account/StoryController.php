<?php

namespace App\Http\Controllers\Account;

use App\Enums\StoryMediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoryRequest;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StoryController extends Controller
{
    public function index()
    {
        $placeIds = auth()->user()->ownedPlaces()->pluck('id');

        $stories = Story::whereIn('place_id', $placeIds)
            ->with(['place:id,name'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return Inertia::render('Account/Stories/Index', [
            'stories' => $stories,
            'places' => auth()->user()->ownedPlaces()->get(['id', 'name']),
        ]);
    }

    public function store(StoreStoryRequest $request)
    {
        $placeId = $request->integer('place_id');
        $place = auth()->user()->ownedPlaces()->findOrFail($placeId);

        $file = $request->file('media');
        $isVideo = str_starts_with($file->getMimeType(), 'video/');
        $path = $file->store('stories/' . now()->format('Y-m-d'), 'public');

        Story::create([
            'user_id'    => auth()->id(),
            'place_id'   => $place->id,
            'media_type' => $isVideo ? StoryMediaType::Video : StoryMediaType::Photo,
            'media_path' => $path,
            'title'      => $request->string('title')->trim()->toString() ?: null,
            'text'       => $request->string('text')->trim()->toString() ?: null,
        ]);

        return back()->with('success', 'История отправлена на модерацию');
    }

    public function destroy(Story $story)
    {
        $placeIds = auth()->user()->ownedPlaces()->pluck('id');
        abort_unless(in_array($story->place_id, $placeIds->all()), 403);

        Storage::disk('public')->delete($story->media_path);
        $story->delete();

        return back()->with('success', 'История удалена');
    }
}
