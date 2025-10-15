<?php

namespace App\Policies;

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
     * @param  mixed $user
     * @param Post $post
     * @return bool
     */
    public function view(User|Share $user, Post $post): bool
    {
        return $user instanceof User
            ? $user->id === $post->user_id
            : $user->collection_id === $post->collection_id;
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
        return $user->id === $post->user_id;
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
        return $user->id === $post->user_id;
    }
}
