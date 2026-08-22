<?php

namespace App\Models;

use App\Enums\StoryMediaType;
use App\Enums\StoryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Story extends Model
{
    protected $fillable = [
        'user_id', 'place_id', 'media_type', 'media_path', 'title', 'text',
        'status', 'published_at', 'expires_at', 'archived_at', 'views_count',
    ];

    protected $casts = [
        'media_type'   => StoryMediaType::class,
        'status'       => StoryStatus::class,
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
        'archived_at'  => 'datetime',
    ];

    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
    public function views(): HasMany   { return $this->hasMany(StoryView::class); }

    public function getMediaUrlAttribute(): string
    {
        return Storage::url($this->media_path);
    }

    /* ------- доменная логика ------- */

    public function approve(): void
    {
        $this->update([
            'status'       => StoryStatus::Approved,
            'published_at' => now(),
            'expires_at'   => now()->addDays(3),
        ]);
    }

    public function reject(): void
    {
        $this->update(['status' => StoryStatus::Rejected]);
    }

    public function archive(): void
    {
        $this->update([
            'status'       => StoryStatus::Archived,
            'archived_at'  => now(),
        ]);
    }

    public function registerView(?int $userId = null, ?string $ip = null): void
    {
        $this->views()->create(['user_id' => $userId, 'ip' => $ip]);
        $this->increment('views_count');
    }
}
