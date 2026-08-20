<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole(['business', 'admin']) || $user->can('create news');
    }

    public function update(User $user, News $news): bool
    {
        return $user->id === $news->place->owner_id || $user->hasRole('admin');
    }

    public function delete(User $user, News $news): bool
    {
        return $this->update($user, $news);
    }
}
