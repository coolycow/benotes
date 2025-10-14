<?php

namespace App\Repositories;

use App\Models\PostTag;
use App\Repositories\Contracts\PostTagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PostTagRepository extends BaseRepository implements PostTagRepositoryInterface
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return PostTag::class;
    }

    /**
     * @param int $postId
     * @return Collection
     */
    public function getByPostId(int $postId): Collection
    {
        return $this->startCondition()
            ->where('post_id', $postId)->get();
    }

    /**
     * @param int $tagId
     * @return Collection
     */
    public function getByTagId(int $tagId): Collection
    {
        return $this->startCondition()
            ->where('tag_id', $tagId)->get();
    }
}
