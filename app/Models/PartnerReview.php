<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PartnerReview extends Model
{
    protected $fillable = [
        'client_id', 'partner_id', 'reviewable_type', 'reviewable_id',
        'rating', 'comment', 'criteria', 'is_public', 'is_verified',
        'admin_note', 'partner_reply', 'replied_at',
    ];

    protected $casts = [
        'rating'       => 'integer',
        'criteria'     => 'array',
        'is_public'    => 'boolean',
        'is_verified'  => 'boolean',
        'replied_at'   => 'datetime',
    ];

    // Связи
    public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function partner(): BelongsTo { return $this->belongsTo(User::class, 'partner_id'); }
    public function reviewable(): MorphTo { return $this->morphTo(); }

    // Валидация рейтинга
    public static function validateRating(int $rating): bool
    {
        return $rating >= 1 && $rating <= 5;
    }

    // Текстовое представление рейтинга
    public function ratingLabel(): string
    {
        return match ($this->rating) {
            5       => 'Отлично',
            4       => 'Хорошо',
            3       => 'Нормально',
            2       => 'Плохо',
            1       => 'Ужасно',
            default => '—',
        };
    }

    // Звёзды для отображения
    public function starsHtml(): string
    {
        $full = str_repeat('★', $this->rating);
        $empty = str_repeat('☆', 5 - $this->rating);
        return $full . $empty;
    }

    // Пересчёт рейтинга партнёра после сохранения/удаления отзыва
    protected static function booted()
    {
        static::saved(function ($review) {
            $review->recalculatePartnerRating();
        });

        static::deleted(function ($review) {
            $review->recalculatePartnerRating();
        });
    }

    public function recalculatePartnerRating(): void
    {
        $partner = $this->partner;
        if (!$partner) return;

        $stats = self::where('partner_id', $partner->id)
            ->where('is_public', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();

        $partner->update([
            'partner_rating' => round($stats->avg_rating ?? 0, 2),
            'reviews_count'  => $stats->total ?? 0,
        ]);
    }
}
