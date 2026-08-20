<?php

namespace App\Concerns;

use App\Enums\ModerationAction;
use App\Models\ModerationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasModerationLogs
{
    public function moderationLogs(): MorphMany
    {
        return $this->morphMany(ModerationLog::class, 'moderatable');
    }

    public function log(ModerationAction $action, ?User $moderator, ?string $comment = null): void
    {
        $this->moderationLogs()->create([
            'moderator_id' => $moderator?->id,
            'action'       => $action,
            'comment'      => $comment,
        ]);
    }

}
