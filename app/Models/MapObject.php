<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapObject extends Model
{
    protected $fillable = [
        'layer_id', 'place_id', 'title', 'description', 'image',
        'lat', 'lng', 'x', 'y', 'is_active', 'sort_order'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'lat' => 'decimal:7', 'lng' => 'decimal:7',
        'x' => 'decimal:2', 'y' => 'decimal:2'
    ];

    public function layer(): BelongsTo { return $this->belongsTo(MapLayer::class); }
    public function place(): BelongsTo { return $this->belongsTo(Place::class); }

    // Умные геттеры: если привязано заведение, берем данные из него, иначе из самого объекта
    public function getDisplayNameAttribute(): string
    {
        return $this->place ? $this->place->name : $this->title;
    }

    public function getDisplayDescriptionAttribute(): ?string
    {
        return $this->place ? ($this->place->short_description ?? $this->place->description) : $this->description;
    }

    public function getDisplayImageAttribute(): ?string
    {
        if ($this->place && $this->place->cover) return $this->place->cover;
        return $this->image;
    }
}
