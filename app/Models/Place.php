<?php

namespace App\Models;

use App\Concerns\Moderatable;
use App\Enums\ModerationStatus;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Place extends Model
{
    use HasFactory, SoftDeletes, Moderatable;

    protected $fillable = [
        'owner_id', 'category_id', 'district_id', 'name', 'slug',
        'short_description', 'description', 'address', 'lat', 'lng',
        'phone', 'email', 'site', 'socials', 'working_hours',
        'price_level', 'status', 'is_featured',
        'external_id', 'external_source',
    ];


    protected $casts = [
        'status'        => ModerationStatus::class,
        'socials'       => 'array',
        'working_hours' => 'array',
        'lat'           => 'decimal:7',
        'lng'           => 'decimal:7',
        'is_featured'   => 'boolean',
    ];


    /* ------- связи ------- */
    public function owner(): BelongsTo    { return $this->belongsTo(User::class, 'owner_id'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }
    public function photos(): HasMany     { return $this->hasMany(PlacePhoto::class)->orderBy('sort'); }
    public function reviews(): HasMany    { return $this->hasMany(Review::class); }
    public function news(): HasMany       { return $this->hasMany(News::class); }
    public function events(): HasMany     { return $this->hasMany(Event::class); }
    public function views(): HasMany      { return $this->hasMany(PlaceView::class); }
    public function stats(): HasMany      { return $this->hasMany(PlaceStat::class); }
    public function placements(): HasMany { return $this->hasMany(FeaturedPlacement::class); }
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withPivot('sort')->orderBy('pivot_sort');
    }

    /* ------- скоупы ------- */
    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('status', ModerationStatus::Approved);
    }

    public function scopeNear(Builder $q, float $lat, float $lng, float $km = 3): Builder
    {
        $distance = "6371 * acos(cos(radians($lat)) * cos(radians(lat)) *
                    cos(radians(lng) - radians($lng)) + sin(radians($lat)) * sin(radians(lat)))";

        return $q->whereNotNull('lat')
            ->selectRaw("places.*, ($distance) as distance")
            ->having('distance', '<=', $km)
            ->orderBy('distance');
    }

    /* ------- доменная логика ------- */
    public function recordView(?int $userId = null, ?string $source = null, ?string $ip = null): void
    {
        $this->views()->create(['user_id' => $userId, 'source' => $source, 'ip' => $ip]);
        $this->increment('views_count');
        $this->stats()->firstOrCreate(['date' => now()->toDateString()])->increment('views');
    }

    public function recalculateRating(): void
    {
        $approved = $this->reviews()->where('status', ReviewStatus::Approved);

        $this->update([
            'rating'        => round((float) (clone $approved)->avg('rating'), 2),
            'reviews_count' => (clone $approved)->count(),
        ]);
    }

    /** Абстрактная карта лендинга: lat/lng → проценты холста (bbox Донецка) */
    public function mapXY(): ?array
    {
        if (! $this->lat || ! $this->lng) return null;

        [$minLat, $maxLat] = [47.92, 48.12];
        [$minLng, $maxLng] = [37.62, 38.02];

        $x = round((($this->lng - $minLng) / ($maxLng - $minLng)) * 100, 2);
        $y = round((1 - ($this->lat - $minLat) / ($maxLat - $minLat)) * 100, 2);

        if ($x < 2 || $x > 98 || $y < 2 || $y > 98) return null;

        return ['x' => $x, 'y' => $y];
    }

    public function isImportedFromMypwa(): bool
    {
        return $this->external_source === 'mypwa.ru' && !empty($this->external_id);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return ($this->photos->firstWhere('is_cover', true) ?? $this->photos->first())?->path;
    }
}
