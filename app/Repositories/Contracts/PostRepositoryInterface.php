<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;

interface PostRepositoryInterface
{
    /**
     * @param int $id
     * @param bool $withTags
     * @return Post|Model|null
     */
    public function getById(int $id, bool $withTags = false): Post|Model|null;

    /**
     * @param int $userId
     * @param int|null $collectionId
     * @return int
     */
    public function getNextOrder(int $userId, ?int $collectionId = null): int;
}
