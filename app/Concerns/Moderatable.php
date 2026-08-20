<?php

namespace App\Concerns;

use App\Enums\ModerationAction;
use App\Enums\ModerationStatus;
use App\Models\ModerationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Moderatable
{
    use HasModerationLogs;

    public function moderationLogs(): MorphMany
    {
        return $this->morphMany(ModerationLog::class, 'moderatable');
    }

    public function moderate(ModerationAction $action, ?User $moderator, ?string $comment = null): static
    {
        $this->moderationLogs()->create([
            'moderator_id' => $moderator?->id,
            'action'       => $action,
            'comment'      => $comment,
        ]);

        $this->status = match ($action) {
            ModerationAction::Approved => ModerationStatus::Approved,
            ModerationAction::Rejected => ModerationStatus::Rejected,
            ModerationAction::Returned => ModerationStatus::Draft,
        };

        if ($this->status === ModerationStatus::Approved && array_key_exists('published_at', $this->getAttributes())) {
            $this->published_at ??= now();
        }

        $this->save();

        return $this;
    }
}
