<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReviewRequest;
use App\Models\Place;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Place $place)
    {
        $place->reviews()->create(
            $request->validated() + [
                'user_id' => $request->user()?->id,
                'status'  => ReviewStatus::Pending,
            ]
        );

        return response()->json([
            'message' => 'Спасибо! Отзыв появится после модерации.',
        ], 201);
    }
}
