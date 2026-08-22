<?php

namespace App\Http\Controllers\Api;

use App\Enums\StoryStatus;
use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function actual()
    {
        $stories = Story::where('status', StoryStatus::Approved)
            ->where('expires_at', '>', now())
            ->with(['place:id,name,slug'])
            ->orderByDesc('published_at')
            ->get()
            ->map(fn ($s) => $this->mapStory($s));

        return response()->json(['data' => $stories]);
    }

    public function archive()
    {
        $stories = Story::where('status', StoryStatus::Archived)
            ->with(['place:id,name,slug'])
            ->orderByDesc('archived_at')
            ->limit(60)
            ->get()
            ->map(fn ($s) => $this->mapStory($s));

        return response()->json(['data' => $stories]);
    }

    public function view(Request $request, Story $story)
    {
        if ($story->status !== StoryStatus::Approved && $story->status !== StoryStatus::Archived) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $story->registerView($request->user()?->id, $request->ip());

        return response()->json(['ok' => true]);
    }

    protected function mapStory(Story $story): array
    {
        return [
            'id'         => $story->id,
            'media_type' => $story->media_type->value,
            'media_url'  => $story->media_url,
            'title'      => $story->title,
            'text'       => $story->text,
            'place'      => $story->place?->only(['id', 'name', 'slug']),
            'published'  => $story->published_at?->diffForHumans(),
            'views'      => $story->views_count,
        ];
    }
}
