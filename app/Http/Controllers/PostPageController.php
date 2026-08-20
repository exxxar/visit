<?php

namespace App\Http\Controllers;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Http\Request;

class PostPageController extends Controller
{
    public function show(Request $request, Post $post)
    {
        // черновик — только автору и админам
        if ($post->status !== PostStatus::Published) {
            $user = $request->user();
            abort_unless($user && ($user->id === $post->author_id || $user->hasRole('admin')), 404);
        }

        $post->load(['author', 'places' => fn ($q) => $q->with(['category', 'district', 'photos'])]);

        return view('post', [
            'post'    => $post,
            'related' => Post::where('status', PostStatus::Published)
                ->where('id', '!=', $post->id)
                ->orderByDesc('published_at')
                ->take(3)->get(),
        ]);
    }
}
