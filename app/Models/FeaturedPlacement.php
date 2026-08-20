<?php

namespace App\Models;

use App\Enums\PlacementSlot;
use App\Enums\PlacementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedPlacement extends Model
{
    use HasFactory;

    protected $fillable = ['place_id', 'slot', 'starts_at', 'ends_at', 'price', 'status'];

    protected $casts = [
        'slot'      => PlacementSlot::class,
        'status'    => PlacementStatus::class,
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'price'     => 'decimal:2',
    ];

    public function place(): BelongsTo { return $this->belongsTo(Place::class); }

    public function scopeActive($q)
    {
        return $q->where('status', PlacementStatus::Active)
            ->whereDate('starts_at', '<=', now())
            ->whereDate('ends_at', '>=', now());
    }
}
