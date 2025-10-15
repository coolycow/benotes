<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Share;
use Illuminate\Auth\Access\HandlesAuthorization;

class SharePolicy
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
     * Determine whether the user can delete the share.
     *
     * @param User $user
     * @param Share $share
     * @return bool
     */
    public function delete(User $user, Share $share): bool
    {
        return $user->getKey() === $share->user_id;
    }
}
