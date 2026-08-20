<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceStat extends Model
{
    public $timestamps = false;

    protected $fillable = ['place_id', 'date', 'views', 'clicks', 'favorites'];

    protected $casts = ['date' => 'date'];

    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
}
