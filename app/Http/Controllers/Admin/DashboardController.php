<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\ModerationStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use App\Models\Lead;
use App\Models\News;
use App\Models\Place;
use App\Models\PlaceView;
use App\Models\Review;
use App\Models\Subscription;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Admin/Dashboard', [
            'queues' => [
                'places'       => Place::where('status', ModerationStatus::OnModeration)->count(),
                'news'         => News::where('status', ModerationStatus::OnModeration)->count(),
                'events'       => Event::where('status', ModerationStatus::OnModeration)->count(),
                'reviews'      => Review::where('status', ReviewStatus::Pending)->count(),
                'applications' => Application::where('status', ApplicationStatus::New)->count(),
            ],
            'viewsSeries' => PlaceView::query()
                ->where('created_at', '>=', now()->subDays(14))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as views')
                ->groupBy('date')->orderBy('date')->get(),
            'topPlaces' => Place::approved()
                ->with('category')->orderByDesc('views_count')->take(5)->get(),
            'counters' => [
                'places'       => Place::approved()->count(),
                'leads_week'   => Lead::where('created_at', '>=', now()->subDays(7))->count(),
                'subscribers'  => Subscription::where('status', 'active')->count(),
            ],
        ]);
    }
}
