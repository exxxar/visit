<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ModerationStatus;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::query()
            ->with('place:id,name')
            ->when($request->q, fn ($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/News/Index', ['news' => $news]);
    }

    public function create()
    {
        return Inertia::render('Admin/News/Edit', [
            'news'   => null,
            'places' => Place::approved()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:200'],
            'body'     => ['required', 'string'],
            'place_id' => ['nullable', 'exists:places,id'],
            'image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status'   => ['required', Rule::enum(ModerationStatus::class)],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('news', 'public');
        }

        $status = ModerationStatus::from($data['status']);

        News::create([
            'title'        => $data['title'],
            'body'         => $data['body'],
            'place_id'     => $data['place_id'] ?? null,
            'image'        => $imagePath,
            'status'       => $status,
            'published_at' => $status->isPublic() ? now() : null,
        ]);

        return redirect()->route('admin.news.index')
            ->with('success', 'Новость создана');
    }

    public function edit(News $news)
    {
        return Inertia::render('Admin/News/Edit', [
            'news'   => $news->load('place:id,name'),
            'places' => Place::approved()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, News $news)
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:200'],
            'body'     => ['required', 'string'],
            'place_id' => ['nullable', 'exists:places,id'],
            'image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status'   => ['required', Rule::enum(ModerationStatus::class)],
        ]);

        $imagePath = $news->image;
        if ($request->hasFile('image')) {
            if ($news->image) {
                Storage::disk('public')->delete($news->image);
            }
            $imagePath = $request->file('image')->store('news', 'public');
        }

        $status = ModerationStatus::from($data['status']);

        $news->update([
            'title'    => $data['title'],
            'body'     => $data['body'],
            'place_id' => $data['place_id'] ?? null,
            'image'    => $imagePath,
            'status'   => $status,
            // при первой публикации проставляем дату
            'published_at' => $status->isPublic() && !$news->published_at
                ? now()
                : $news->published_at,
        ]);

        return redirect()->route('admin.news.index')
            ->with('success', 'Новость обновлена');
    }

    public function destroy(News $news)
    {
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }
        $news->delete();

        return back()->with('success', 'Новость удалена');
    }
}
