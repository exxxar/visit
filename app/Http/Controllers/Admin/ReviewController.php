<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerReview;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = PartnerReview::query()
            ->with([
                'client:id,name,email',
                'partner:id,name,email,partner_rating',
                'reviewable:id,title',
            ])
            ->latest();

        // Фильтры
        $query->when($request->rating, fn($q, $r) => $q->where('rating', $r));
        $query->when($request->public === 'yes', fn($q) => $q->where('is_public', true));
        $query->when($request->public === 'no', fn($q) => $q->where('is_public', false));

        $query->when($request->search, function($q, $s) {
            $q->where(function($w) use ($s) {
                $w->whereHas('client', fn($u) => $u->where('name', 'like', "%$s%"))
                    ->orWhereHas('partner', fn($u) => $u->where('name', 'like', "%$s%"))
                    ->orWhere('comment', 'like', "%$s%");
            });
        });

        $reviews = $query->paginate(25)->withQueryString();

        $stats = [
            'total'    => PartnerReview::count(),
            'public'   => PartnerReview::where('is_public', true)->count(),
            'hidden'   => PartnerReview::where('is_public', false)->count(),
            'avg'      => round(PartnerReview::where('is_public', true)->avg('rating'), 2),
        ];

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'stats'   => $stats,
            'filters' => $request->only(['rating', 'public', 'search']),
        ]);
    }

    public function togglePublic(PartnerReview $review)
    {
        $old = ['is_public' => $review->is_public];
        $review->update(['is_public' => !$review->is_public]);

        AdminAuditLog::log(
            'review_toggle_public',
            $review,
            $old,
            ['is_public' => $review->is_public],
            $review->is_public ? 'Скрыт с публичной страницы' : 'Опубликован'
        );

        return back()->with('success', $review->is_public ? 'Отзыв опубликован' : 'Отзыв скрыт');
    }

    public function verify(PartnerReview $review)
    {
        $old = ['is_verified' => $review->is_verified];
        $review->update(['is_verified' => !$review->is_verified]);

        AdminAuditLog::log('review_verify', $review, $old, ['is_verified' => $review->is_verified]);

        return back()->with('success', $review->is_verified ? 'Отзыв проверен' : 'Проверка снята');
    }

    public function destroy(PartnerReview $review)
    {
        AdminAuditLog::log('review_delete', $review, $review->toArray(), null, 'Удалён навсегда');

        $review->delete();

        return back()->with('success', 'Отзыв удалён');
    }

    public function addNote(Request $request, PartnerReview $review)
    {
        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        $old = ['admin_note' => $review->admin_note];
        $review->update(['admin_note' => $data['admin_note']]);

        AdminAuditLog::log('review_note', $review, $old, ['admin_note' => $data['admin_note']]);

        return back()->with('success', 'Заметка сохранена');
    }
}
