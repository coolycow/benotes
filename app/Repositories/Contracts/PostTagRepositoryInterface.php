<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface PostTagRepositoryInterface
{
    /**
     * @param int $postId
     * @return Collection
     */
    public function getByPostId(int $postId): Collection;

    /**
     * @param int $tagId
     * @return Collection
     */
    public function getByTagId(int $tagId): Collection;
}
