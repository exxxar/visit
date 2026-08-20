<?php

namespace App\Models;

use App\Concerns\Moderatable;
use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use HasFactory, SoftDeletes, Moderatable;

    protected $fillable = ['place_id', 'title', 'body', 'image', 'status', 'published_at'];

    protected $casts = [
        'status'       => ModerationStatus::class,
        'published_at' => 'datetime',
    ];

    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
}
