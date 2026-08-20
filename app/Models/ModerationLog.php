<?php

namespace App\Models;

use App\Enums\ModerationAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModerationLog extends Model
{
    use HasFactory;

    protected $fillable = ['moderatable_type', 'moderatable_id', 'moderator_id', 'action', 'comment'];

    protected $casts = ['action' => ModerationAction::class];

    public function moderatable(): MorphTo  { return $this->morphTo(); }
    public function moderator(): BelongsTo  { return $this->belongsTo(User::class, 'moderator_id'); }
}
