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
            'title'  => ['required', 'string', 'max:160'],
            'tag'    => ['nullable', 'string', 'max:50'],
            'excerpt'=> ['nullable', 'string', 'max:300'],
            'body'   => ['nullable', 'string'],
            'cover'  => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
            'places' => ['nullable', 'array'],
        ]);

        $post = Post::create([
            ...$data,
            'author_id' => $request->user()->id,
            'slug'      => Str::slug($data['title']),
            'status'    => PostStatus::from($data['status']),
            'published_at' => $data['status'] === 'published' ? now() : null,
        ]);

        $post->places()->sync($data['places'] ?? []);

        return back()->with('success', 'Подборка сохранена');
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title'  => ['required', 'string', 'max:160'],
            'tag'    => ['nullable', 'string', 'max:50'],
            'excerpt'=> ['nullable', 'string', 'max:300'],
            'body'   => ['nullable', 'string'],
            'cover'  => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
            'places' => ['nullable', 'array'],
        ]);

        $post->update([
            ...$data,
            'status' => PostStatus::from($data['status']),
            'published_at' => $post->published_at
                    ?? ($data['status'] === 'published' ? now() : null),
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
