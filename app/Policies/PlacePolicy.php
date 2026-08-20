<?php

namespace App\Policies;

use App\Models\Place;
use App\Models\User;

class PlacePolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // публичная выдача фильтруется скоупом approved
    }

    public function view(?User $user, Place $place): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['business', 'editor', 'admin']);
    }

    /** Владелец карточки или админ */
    public function update(User $user, Place $place): bool
    {
        return $user->id === $place->owner_id || $user->hasRole('admin');
    }

    public function delete(User $user, Place $place): bool
    {
        return $user->id === $place->owner_id || $user->hasRole('admin');
    }

    /** Владелец может управлять новостями/аналитикой своего места */
    public function manageContent(User $user, Place $place): bool
    {
        return $user->id === $place->owner_id || $user->hasRole('admin');
    }
}
