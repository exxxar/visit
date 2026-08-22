<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoryView extends Model
{
    public $timestamps = false;

    protected $fillable = ['story_id', 'user_id', 'ip', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function story(): BelongsTo { return $this->belongsTo(Story::class); }

    protected static function booted(): void
    {
        static::creating(fn ($view) => $view->created_at = now());
    }
}
