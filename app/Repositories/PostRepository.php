<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return Post::class;
    }

    /**
     * @param int $id
     * @param bool $withTags
     * @return Post|Model|null
     */
    public function getById(int $id, bool $withTags = false): Post|Model|null
    {
        return $this->startCondition()
            ->with($withTags ? ['tags:id,name'] : [])
            ->find($id);
    }

    /**
     * @param int $userId
     * @param int|null $collectionId
     * @return int
     */
    public function getNextOrder(int $userId, ?int $collectionId = null): int
    {
        return $this->startCondition()
                ->where('user_id', $userId)
                ->where('collection_id', $collectionId)
                ->max('order') + 1;
    }

    /**
     * @param int $userId
     * @param int $type
     * @return bool
     */
    public function hasUncategorizedWithType(int $userId, int $type): bool
    {
        return $this->startCondition()
            ->where('user_id', $userId)
            ->whereNull('collection_id')
            ->where('type', $type)
            ->exists();
    }
}
