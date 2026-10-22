<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlaceReview;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewModerationController extends Controller
{
    public function index(Request $request)
    {
        $query = PlaceReview::with([
            'place:id,name,slug',
            'user:id,name',
            'reports' => function ($q) {
                $q->where('status', 'pending'); // Показываем только активные жалобы
            }
        ]);

        // Фильтр по статусу (по умолчанию показываем pending)
        $status = $request->get('status', 'pending');
        $query->where('status', $status);

        // Поиск по тексту или имени пользователя
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('place', fn($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();

        // Статистика для верхних карточек
        $stats = [
            'pending' => PlaceReview::where('status', 'pending')->count(),
            'approved' => PlaceReview::where('status', 'approved')->count(),
            'rejected' => PlaceReview::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Reviews/Moderation', [
            'reviews' => $reviews,
            'stats'   => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function approve(PlaceReview $review)
    {
        $oldStatus = $review->status;
        $review->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        AdminAuditLog::log('review_approved', $review, ['status' => $oldStatus], ['status' => 'approved']);

        return back()->with('success', 'Отзыв одобрен и опубликован');
    }

    public function reject(Request $request, PlaceReview $review)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $oldStatus = $review->status;
        $review->update([
            'status' => 'rejected',
            'moderator_comment' => $data['comment'],
        ]);

        AdminAuditLog::log('review_rejected', $review,
            ['status' => $oldStatus],
            ['status' => 'rejected', 'comment' => $data['comment']]
        );

        return back()->with('success', 'Отзыв отклонен');
    }

    public function destroy(PlaceReview $review)
    {
        AdminAuditLog::log('review_deleted', $review, $review->toArray(), null, 'Удалено модератором');

        $review->delete(); // Хук в модели автоматически пересчитает рейтинг заведения

        return back()->with('success', 'Отзыв полностью удален');
    }
}
