<?php

namespace App\Models;

use App\Concerns\HasModerationLogs;
use App\Enums\ReviewStatus;
use App\Observers\ReviewObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ReviewObserver::class)]
class Review extends Model
{
    use HasFactory, HasModerationLogs;

    protected $fillable = ['place_id', 'user_id', 'author_name', 'rating', 'text', 'status'];

    protected $casts = ['status' => ReviewStatus::class, 'rating' => 'integer'];

    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
}
