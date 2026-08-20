<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceView extends Model
{
    const UPDATED_AT = null;   // в таблице только created_at — его Laravel напишет сам

    protected $fillable = ['place_id', 'user_id', 'ip', 'source'];

    protected $casts = ['created_at' => 'datetime'];

    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
}
