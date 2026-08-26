<?php

namespace App\Http\Controllers\Account;

use App\Enums\ModerationStatus;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\News;
use App\Models\PlaceStat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $places = $request->user()->ownedPlaces()->with(['category', 'district', 'photos'])->get();
        $ids    = $places->pluck('id');

        return Inertia::render('Account/Dashboard', [
            'places' => $places,
            'series' => PlaceStat::whereIn('place_id', $ids)
                ->where('date', '>=', now()->subDays(14))
                ->selectRaw('date, SUM(views) views, SUM(clicks) clicks')
                ->groupBy('date')->orderBy('date')->get(),
            'totals' => PlaceStat::whereIn('place_id', $ids)
                ->where('date', '>=', now()->subDays(30))
                ->selectRaw('SUM(views) views, SUM(clicks) clicks, SUM(favorites) favs')
                ->first(),
            'newsCount'    => News::whereIn('place_id', $ids)->count(),
            'onModeration' => $places->where('status', ModerationStatus::OnModeration)->count()
                + News::whereIn('place_id', $ids)->where('status', ModerationStatus::OnModeration)->count(),
            'feedback' => [
                'new'         => Feedback::where('status', Feedback::STATUS_NEW)->count(),
                'in_progress' => Feedback::where('status', Feedback::STATUS_PROGRESS)->count(),
                'resolved'    => Feedback::where('status', Feedback::STATUS_RESOLVED)->count(),
                'week'        => Feedback::where('created_at', '>=', now()->subDays(7))->count(),
                'recent'      => Feedback::latest()
                    ->limit(3)
                    ->get(['id', 'name', 'subject', 'status', 'created_at'])
                    ->map(fn ($f) => [
                        'id'         => $f->id,
                        'name'       => $f->name,
                        'subject'    => $f->subject,
                        'subjectLabel' => $f->subjectLabel(),
                        'created_at' => $f->created_at->toISOString(),
                    ]),
            ],
        ]);
    }
}
