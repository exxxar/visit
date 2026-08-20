<?php

namespace App\Http\Controllers\Account;

use App\Enums\ModerationStatus;
use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $ids = $request->user()->ownedPlaces()->pluck('id');

        return Inertia::render('Account/News/Index', [
            'news'   => News::whereIn('place_id', $ids)->with('place')->latest()->paginate(10),
            'places' => $request->user()->ownedPlaces()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', News::class);

        $data = $request->validate([
            'place_id' => ['required', 'exists:places,id'],
            'title'    => ['required', 'string', 'max:160'],
            'body'     => ['required', 'string', 'max:5000'],
            'image'    => ['nullable', 'string'],
        ]);

        $place = $request->user()->ownedPlaces()->findOrFail($data['place_id']);

        $place->news()->create([
            'title'  => $data['title'],
            'body'   => $data['body'],
            'image'  => $data['image'] ?? null,
            'status' => ModerationStatus::OnModeration,
        ]);

        return back()->with('success', 'Новость отправлена на модерацию');
    }

    public function destroy(Request $request, News $news)
    {
        $this->authorize('update', $news);

        $news->delete();

        return back()->with('success', 'Новость удалена');
    }
}
