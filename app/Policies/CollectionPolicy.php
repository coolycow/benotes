<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Collection;
use Illuminate\Auth\Access\HandlesAuthorization;

class CollectionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view the collection.
     *
     * @param User $user
     * @param Collection $collection
     * @return bool
     */
    public function view(User $user, Collection $collection): bool
    {
        return $user->id === $collection->user_id;
    }

    /**
     * Determine whether the user can update the collection.
     *
     * @param User $user
     * @param Collection $collection
     * @return bool
     */
    public function update(User $user, Collection $collection): bool
    {
        return $user->id === $collection->user_id;
    }

    /**
     * Determine whether the user can delete the collection.
     *
     * @param User $user
     * @param Collection $collection
     * @return bool
     */
    public function delete(User $user, Collection $collection): bool
    {
        return $user->id === $collection->user_id;
    }

    /**
     * Determine whether the user can inherit the collection.
     *
     * @param User $user
     * @param Collection $collection
     * @return bool
     */
    public function inherit(User $user, Collection $collection): bool
    {
        return $user->id === $collection->user_id;
    }

    /**
     * @param User $user
     * @param Collection $collection
     * @return bool
     */
    public function share(User $user, Collection $collection): bool
    {
        return $user->id === $collection->user_id;
    }
}
