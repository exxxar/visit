<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\ReviewReply;
use App\Models\ReviewReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PlaceReviewController extends Controller
{
    public function index(Request $request, Place $place)
    {
        $reviews = $place->approvedReviews()
            ->with([
                'user:id,name',          // <-- БЫЛО: 'user:id,name,avatar'
                'reply.user:id,name'     // <-- БЫЛО: 'reply.user:id,name,avatar'
            ])
            ->withCount('reports')
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    public function store(Request $request, Place $place)
    {
        $user = Auth::user();

        // Проверка: владелец не может отзываться о своём месте
        if ($place->user_id === $user->id) {
            return response()->json([
                'message' => 'Вы не можете оставить отзыв о своём заведении'
            ], 403);
        }

        // Проверка: уже есть отзыв от этого пользователя
        if ($place->reviews()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'Вы уже оставили отзыв об этом заведении'
            ], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|min:10|max:2000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Загрузка фото
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('reviews', 'public');
                $photoPaths[] = $path;
            }
        }

        $review = PlaceReview::create([
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'content' => $validated['content'],
            'photos' => $photoPaths ?: null,
            'status' => 'pending', // На модерацию
        ]);

        return response()->json([
            'message' => 'Отзыв отправлен на модерацию. Он появится после проверки (до 24 часов).',
            'review' => $review->load('user:id,name') // <-- БЫЛО: 'user:id,name,avatar'
        ], 201);
    }

    public function update(Request $request, PlaceReview $review)
    {
        $user = Auth::user();

        // Только автор может редактировать
        if ($review->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'content' => 'sometimes|string|min:10|max:2000',
        ]);

        $review->update($validated);
        $review->update(['status' => 'pending']); // Снова на модерацию

        return response()->json([
            'message' => 'Отзыв обновлён и отправлен на модерацию',
            'review' => $review->fresh()
        ]);
    }

    public function destroy(PlaceReview $review)
    {
        $user = Auth::user();

        // Только автор или админ может удалить
        if ($review->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Удаляем фото
        if ($review->photos) {
            foreach ($review->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $review->delete();

        return response()->json(['message' => 'Отзыв удалён']);
    }

    // Ответ владельца
    public function reply(Request $request, PlaceReview $review)
    {
        $user = Auth::user();

        // Только владелец заведения может ответить
        if ($review->place->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Проверка: уже есть ответ
        if ($review->reply) {
            return response()->json(['message' => 'Вы уже ответили на этот отзыв'], 422);
        }

        $validated = $request->validate([
            'content' => 'required|string|min:10|max:1000',
        ]);

        $reply = ReviewReply::create([
            'review_id' => $review->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Ответ опубликован',
            'reply' => $reply->load('user:id,name')
        ], 201);
    }

    // Жалоба на отзыв
    public function report(Request $request, PlaceReview $review)
    {
        $user = Auth::user();

        // Проверка: уже жаловался
        if ($review->reports()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Вы уже пожаловались на этот отзыв'], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|in:spam,offensive,fake,other',
            'description' => 'nullable|string|max:500',
        ]);

        ReviewReport::create([
            'review_id' => $review->id,
            'user_id' => $user->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json(['message' => 'Жалоба отправлена на рассмотрение'], 201);
    }
}
