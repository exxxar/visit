<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacePhoto extends Model
{
    use HasFactory;

    protected $fillable = ['place_id', 'path', 'is_cover', 'sort'];

    protected $casts = ['is_cover' => 'boolean'];

    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
}
