<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(?User $user): bool
    {
        return true;
    }

    /** Модерация — через permission spatie */
    public function moderate(User $user): bool
    {
        return $user->can('moderate reviews');
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->can('moderate reviews');
    }
}
