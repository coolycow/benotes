<?php

namespace App\Policies;

use App\Enums\SharePermissionEnum;
use App\Models\User;
use App\Models\Post;
use App\Models\Share;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
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
     * Determine whether the user can view the post.
     *
     * @param  User|Share $user
     * @param Post $post
     * @return bool
     */
    public function view(User|Share $user, Post $post): bool
    {
        if ($user instanceof Share) {
            return $user->collection_id === $post->collection_id;
        }

        return $post->user_id === $user->getKey()
            || $post->collection->user_id === $user->getKey()
            || $post->collection->shares()
                ->where('guest_id', $user->getKey())
                ->exists();
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param User $user
     * @param Post $post
     * @return bool
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id
            || $post->collection->user_id === $user->id
            || $post->collection->shares()
                ->where('guest_id', $user->id)
                ->whereIn('permission', [
                    SharePermissionEnum::ReadAndWrite,
                    SharePermissionEnum::ReadAndWriteAndDelete
                ])
                ->exists();
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param User $user
     * @param Post $post
     * @return bool
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id
            || $post->collection->user_id === $user->id
            || $post->collection->shares()
                ->where('guest_id', $user->id)
                ->whereIn('permission', SharePermissionEnum::ReadAndWriteAndDelete)
                ->exists();
    }

    /**
     * Determine whether the user can move the post to another collection.
     *
     * @param User $user
     * @param Post $post
     * @return bool
     */
    public function movePost(User $user, Post $post): bool
    {
        return $user->id === $post->user_id ||
            $post->collection->user_id === $user->id ||
            $post->collection->shares()
                ->where('guest_id', $user->id)
                ->whereIn('permission', [SharePermissionEnum::ReadAndWriteAndDelete])
                ->exists();
    }
}
