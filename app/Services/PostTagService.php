<?php

namespace App\Services;

use App\Models\PostTag;
use App\Repositories\Contracts\PostTagRepositoryInterface;

readonly class PostTagService
{

    public function __construct(
        protected PostTagRepositoryInterface $postTagRepository
    )
    {
        //
    }

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

    /**
     * @param int $post_id
     * @param array $tag_ids
     * @return void
     */
    public function saveTags(int $post_id, array $tag_ids): void
    {
        $old_tags_obj = $this->postTagRepository->getByPostId($post_id);
        $old_tags = [];

        foreach ($old_tags_obj as $old_tag) {
            $old_tags[] = $old_tag->tag_id;
        }

        foreach ($tag_ids as $tag_id) {
            if (!in_array($tag_id, $old_tags)) {
                $this->create($post_id, $tag_id);
            }
        }

        $this->deleteByPostIdAndTagIds($post_id, $tag_ids);
    }
}
