<?php

namespace App\Policies;

use App\Enums\UserPermissionEnum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
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
     * Determine whether the user can create users.
     *
     * @param User $authUser
     * @return bool
     */
    public function create(User $authUser): bool
    {
        return $authUser->permission === UserPermissionEnum::Admin;
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param User $authUser
     * @param User $user
     * @return Response
     */
    public function update(User $authUser, User $user): Response
    {
        return $authUser->id === $user->id
            ? Response::allow()
            : Response::deny('Only the user itself can change these information.');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param User $authUser
     * @return bool
     */
    public function delete(User $authUser): bool
    {
        return $authUser->permission === UserPermissionEnum::Admin;
    }
}
