<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['business', 'editor', 'admin']);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->id === $event->author_id
            || ($event->place && $user->id === $event->place->owner_id)
            || $user->hasRole('admin');
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
