<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StoryMediaType;
use App\Enums\StoryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoryRequest;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $stories = Story::with(['place:id,name', 'user:id,name'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Stories/Index', ['stories' => $stories]);
    }

    public function store(StoreStoryRequest $request)
    {
        $file = $request->file('media');
        $isVideo = str_starts_with($file->getMimeType(), 'video/');
        $path = $file->store('stories/' . now()->format('Y-m-d'), 'public');

        $story = Story::create([
            'user_id'    => auth()->id(),
            'place_id'   => $request->integer('place_id') ?: null,
            'media_type' => $isVideo ? StoryMediaType::Video : StoryMediaType::Photo,
            'media_path' => $path,
            'title'      => $request->string('title')->trim()->toString() ?: null,
            'text'       => $request->string('text')->trim()->toString() ?: null,
        ]);

        // админ публикует сразу
        $story->approve();

        return back()->with('success', 'История опубликована');
    }

    public function act(Request $request, Story $story)
    {
        match ($request->input('action')) {
            'approve' => $story->approve(),
            'reject'  => $story->reject(),
            'archive' => $story->archive(),
            default   => null,
        };

        return back()->with('success', 'Статус истории обновлён');
    }

    public function destroy(Story $story)
    {
        Storage::disk('public')->delete($story->media_path);
        $story->delete();

        return back()->with('success', 'История удалена безвозвратно');
    }
}
