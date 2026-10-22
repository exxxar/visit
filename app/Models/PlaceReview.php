<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PlaceReview extends Model
{
    // Явно указываем имя таблицы, чтобы избежать конфликтов
    protected $table = 'place_reviews';

    protected $fillable = [
        'place_id',
        'user_id',
        'rating',
        'content',
        'photos',
        'status',
        'moderator_comment',
        'approved_at',
    ];

    protected $casts = [
        'photos' => 'array',
        'approved_at' => 'datetime',
        'rating' => 'integer',
    ];

    protected $appends = ['photos_urls'];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reply(): HasOne
    {
        return $this->hasOne(ReviewReply::class, 'review_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class, 'review_id');
    }

    public function getPhotosUrlsAttribute(): array
    {
        if (!$this->photos) {
            return [];
        }
        return array_map(fn($path) => Storage::disk('public')->url($path), $this->photos);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    protected static function booted()
    {
        static::created(function ($review) {
            if ($review->status === 'approved') {
                self::recalculatePlaceStats($review->place_id);
            }
        });

        static::updated(function ($review) {
            if ($review->isDirty('status')) {
                self::recalculatePlaceStats($review->place_id);
            }
        });

        static::deleted(function ($review) {
            self::recalculatePlaceStats($review->place_id);
        });
    }

    private static function recalculatePlaceStats(int $placeId): void
    {
        $stats = self::where('place_id', $placeId)
            ->where('status', 'approved')
            ->selectRaw('COUNT(*) as count, AVG(rating) as avg_rating')
            ->first();

        Place::where('id', $placeId)->update([
            'reviews_count' => $stats->count ?? 0,
            'rating' => round($stats->avg_rating ?? 0, 1),
        ]);
    }
}
