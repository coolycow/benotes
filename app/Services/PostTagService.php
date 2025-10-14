<?php

namespace App\Services;

use App\Models\PostTag;

readonly class PostTagService
{
    /**
     * @param int $postId
     * @param string $tagId
     * @return PostTag
     */
    public function create(int $postId, string $tagId): PostTag
    {
        return PostTag::query()->create([
            'post_id' => $postId,
            'tag_id'  => $tagId
        ]);
    }

    /**
     * @param int $postId
     * @param array $tagIds
     * @return mixed
     */
    public function deleteByPostIdAndTagIds(int $postId, array $tagIds = []): mixed
    {
        return PostTag::query()
            ->where('post_id', $postId)
            ->whereNotIn('tag_id', $tagIds)
            ->delete();
    }
}
