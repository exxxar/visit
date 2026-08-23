<?php

namespace App\Models;

use App\Concerns\Moderatable;
use App\Enums\EventType;
use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes, Moderatable;

    protected $fillable = ['place_id', 'author_id', 'title', 'slug', 'description',
        'type', 'starts_at', 'ends_at', 'image', 'price', 'status', 'meta',];

    protected $casts = [
        'type'      => EventType::class,
        'status'    => ModerationStatus::class,
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'meta' => 'array',
    ];

    public function place(): BelongsTo  { return $this->belongsTo(Place::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }

    public function scopeUpcoming($q) { return $q->where('starts_at', '>=', now()); }
}
