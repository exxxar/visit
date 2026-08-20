<?php

namespace App\Http\Controllers\Api;

use App\Enums\PostStatus;
use App\Http\Resources\PostResource;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        return PostResource::collection(
            Post::query()
                ->where('status', PostStatus::Published)
                ->orderByDesc('published_at')
                ->get()
        );
    }

    public function show(string $slug)
    {
        $post = Post::query()
            ->where('status', PostStatus::Published)
            ->where('slug', $slug)
            ->with(['places' => fn ($q) => $q->approved()->with('category')])
            ->firstOrFail();

        return new PostResource($post);
    }
}
