<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    public function saved(Review $review): void
    {
        if ($review->wasChanged('status')) {
            $review->place->recalculateRating();
        }
    }

    public function deleted(Review $review): void
    {
        $review->place->recalculateRating();
    }
}
