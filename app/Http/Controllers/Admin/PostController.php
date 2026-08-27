<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Posts/Index', [
            'posts' => Post::with('author')->latest()->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => ['required', 'string', 'max:200'],
            'tag'     => ['nullable', 'string', 'max:60'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body'    => ['required', 'string'],
            'cover'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status'  => ['required', 'in:draft,published'],
            'places'  => ['array'],
            'places.*' => ['integer', 'exists:places,id'],
        ]);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('posts', 'public');
        }

        $post = Post::create([
            'title'   => $data['title'],
            'tag'     => $data['tag'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
            'body'    => $data['body'],
            'cover'   => $coverPath,
            'status'  => $data['status'],
        ]);

        if (!empty($data['places'])) {
            $post->places()->sync($data['places']);
        }

        return back()->with('success', 'Подборка создана');
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title'   => ['required', 'string', 'max:200'],
            'tag'     => ['nullable', 'string', 'max:60'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body'    => ['required', 'string'],
            'cover'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status'  => ['required', 'in:draft,published'],
            'places'  => ['array'],
            'places.*' => ['integer', 'exists:places,id'],
        ]);

        $coverPath = $post->cover;
        if ($request->hasFile('cover')) {
            // удаляем старую картинку, если была
            if ($post->cover) {
                Storage::disk('public')->delete($post->cover);
            }
            $coverPath = $request->file('cover')->store('posts', 'public');
        }

        $post->update([
            'title'   => $data['title'],
            'tag'     => $data['tag'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
            'body'    => $data['body'],
            'cover'   => $coverPath,
            'status'  => $data['status'],
        ]);

        $post->places()->sync($data['places'] ?? []);

        return back()->with('success', 'Подборка обновлена');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('success', 'Подборка удалена');
    }
}
