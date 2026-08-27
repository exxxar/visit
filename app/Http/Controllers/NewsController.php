<?php

namespace App\Http\Controllers;

use App\Enums\ModerationStatus;
use App\Models\News;
use App\Models\Place;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::query()
            ->where('status', ModerationStatus::Approved)
            ->whereNotNull('published_at')
            ->with('place:id,name,slug')
            ->when($request->place, fn ($q, $p) => $q->where('place_id', $p))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $places = Place::query()
            ->where('status', ModerationStatus::Approved)
            ->whereHas('news', fn ($q) => $q->where('status', ModerationStatus::Approved))
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('news', compact('news', 'places'));
    }

    public function show(News $news)
    {
        abort_unless($news->status === ModerationStatus::Approved, 404);

        $news->load('place:id,name,slug');

        $related = News::query()
            ->where('status', ModerationStatus::Approved)
            ->whereNotNull('published_at')
            ->where('id', '!=', $news->id)
            ->latest('published_at')
            ->limit(3)
            ->get(['id', 'title', 'body', 'image', 'published_at']);

        return view('news-show', compact('news', 'related'));
    }
}
