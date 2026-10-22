<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\DevelopmentRequest;
use App\Models\PromotionRequest;
use App\Models\PartnerReview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * Форма отзыва (показывается после завершения заявки)
     */
    public function create(Request $request, string $type, int $id)
    {
        $modelClass = $type === 'promotion' ? PromotionRequest::class : DevelopmentRequest::class;
        $partnerField = $type === 'promotion' ? 'partner_id' : 'tech_partner_id';

        $reviewRequest = $modelClass::with(['partner:id,name,slug,partner_rating,reviews_count'])
            ->findOrFail($id);

        // Проверка прав
        abort_unless($reviewRequest->user_id === $request->user()->id, 403);
        abort_unless($reviewRequest->status === 'completed', 422, 'Отзыв можно оставить только после завершения');
        abort_unless($reviewRequest->{$partnerField}, 422, 'Заявка не была выполнена партнёром');

        // Проверка, не оставлен ли уже отзыв
        $existingReview = PartnerReview::where('client_id', $request->user()->id)
            ->where('reviewable_type', $modelClass)
            ->where('reviewable_id', $id)
            ->first();

        return Inertia::render('Account/Review/Create', [
            'request' => [
                'id'        => $reviewRequest->id,
                'type'      => $type,
                'title'     => $reviewRequest->title,
                'amount'    => (float) $reviewRequest->amount,
                'completed_at' => $reviewRequest->updated_at->toDateTimeString(),
            ],
            'partner' => [
                'id'              => $reviewRequest->{$partnerField},
                'name'            => $reviewRequest->partner->name,
                'slug'            => $reviewRequest->partner->slug,
                'rating'          => (float) $reviewRequest->partner->partner_rating,
                'reviews_count'   => $reviewRequest->partner->reviews_count,
            ],
            'existingReview' => $existingReview ? [
                'rating'  => $existingReview->rating,
                'comment' => $existingReview->comment,
            ] : null,
        ]);
    }

    /**
     * Сохранение отзыва
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id'   => ['required', 'integer'],
            'request_type' => ['required', 'in:promotion,development'],
            'rating'       => ['required', 'integer', 'min:1', 'max:5'],
            'comment'      => ['nullable', 'string', 'max:1000'],
            'criteria'     => ['nullable', 'array'],
            'criteria.*'   => ['integer', 'min:1', 'max:5'],
        ], [
            'rating.required' => 'Пожалуйста, оцените работу партнёра',
            'rating.min'      => 'Минимальная оценка — 1 звезда',
            'rating.max'      => 'Максимальная оценка — 5 звёзд',
            'comment.max'     => 'Отзыв не должен превышать 1000 символов',
        ]);

        $modelClass = $data['request_type'] === 'promotion'
            ? PromotionRequest::class
            : DevelopmentRequest::class;

        $partnerField = $data['request_type'] === 'promotion'
            ? 'partner_id'
            : 'tech_partner_id';

        $reviewRequest = $modelClass::findOrFail($data['request_id']);

        // Проверка прав
        abort_unless($reviewRequest->user_id === $request->user()->id, 403);
        abort_unless($reviewRequest->status === 'completed', 422);
        abort_unless($reviewRequest->{$partnerField}, 422);

        // Создание или обновление отзыва
        $review = PartnerReview::updateOrCreate(
            [
                'client_id'       => $request->user()->id,
                'reviewable_type' => $modelClass,
                'reviewable_id'   => $reviewRequest->id,
            ],
            [
                'partner_id' => $reviewRequest->{$partnerField},
                'rating'     => $data['rating'],
                'comment'    => $data['comment'],
                'criteria'   => $data['criteria'] ?? null,
                'is_public'  => true,
            ]
        );

        return redirect()
            ->route('account.dashboard')
            ->with('success', 'Спасибо за отзыв! Он поможет другим клиентам выбрать надёжного партнёра.');
    }
}
