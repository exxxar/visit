<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PartnerReview;
use App\Models\PromotionRequest;
use App\Models\DevelopmentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnerController extends Controller
{

    public function index(Request $request)
    {
        // Показываем только тех, кто реально работает (есть рейтинг или завершённые заказы)
        $partners = User::where(function ($q) {
            $q->whereHas('roles', fn($r) => $r->whereIn('name', ['tech_partner', 'ad_partner']))
                ->orWhereNotNull('meta->partner_type');
        })
            ->where(function ($q) {
                $q->where('partner_rating', '>', 0)
                    ->orWhere('completed_orders_count', '>', 0);
            })
            ->orderByDesc('partner_rating')
            ->orderByDesc('completed_orders_count')
            ->paginate(12);

        return view('public.partners', compact('partners'));
        // Если используешь Inertia для публичных страниц, замени на:
        // return Inertia::render('Public/Partners', compact('partners'));
    }

    public function show(string $slug)
    {
        $partner = User::where('slug', $slug)
            ->where(function ($q) {
                $q->whereHas('roles', fn($r) => $r->whereIn('name', ['tech_partner', 'ad_partner']))
                    ->orWhereNotNull('meta->partner_type');
            })
            ->firstOrFail();

        // Публичные отзывы
        $reviews = PartnerReview::where('partner_id', $partner->id)
            ->where('is_public', true)
            ->with('client:id,name')
            ->latest()
            ->paginate(10);

        // Статистика завершённых заказов
        $promoCompleted = PromotionRequest::where('partner_id', $partner->id)
            ->where('status', 'completed')
            ->count();

        $devCompleted = DevelopmentRequest::where('tech_partner_id', $partner->id)
            ->where('status', 'completed')
            ->count();

        $totalCompleted = $promoCompleted + $devCompleted;

        // Распределение оценок
        $ratingDistribution = PartnerReview::where('partner_id', $partner->id)
            ->where('is_public', true)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Заполняем пропуски нулями
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = $ratingDistribution[$i] ?? 0;
        }

        return Inertia::render('Public/Partner', [
            'partner' => [
                'name'              => $partner->name,
                'slug'              => $partner->slug,
                'bio'               => $partner->bio,
                'partner_rating'    => (float) $partner->partner_rating,
                'reviews_count'     => $partner->reviews_count,
                'completed_orders'  => $totalCompleted,
                'partner_level'     => $partner->partner_level,
                'level_name'        => config("partners.levels.{$partner->partner_level}.name"),
                'portfolio_url'     => $partner->portfolio_url,
                'member_since'      => $partner->created_at->format('F Y'),
            ],
            'reviews' => $reviews,
            'ratingDistribution' => $ratingDistribution,
        ]);
    }
}
