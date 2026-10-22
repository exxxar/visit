<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\PlaceReview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyReviewsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $reviews = PlaceReview::where('user_id', $user->id)
            ->with('place:id,name,slug')
            ->latest()
            ->paginate(10);

        return Inertia::render('Account/Tourist/Reviews', [
            'reviews' => $reviews,
        ]);
    }
}
