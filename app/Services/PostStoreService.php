<?php

namespace App\Services;

use App\Enums\PostTypeEnum;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;

readonly class PostStoreService
{
    public function __construct(
        protected PostRepositoryInterface $repository,
        protected PostService $postService,
        protected PostTagService $postTagService,
        protected PostImageService $postImageService,
    )
    {
        //
    }

    /**
     * @param int $user_id
     * @param string $content
     * @param string|null $title
     * @param int|null $collection_id
     * @param string|null $description
     * @param array|null $tags
     * @return Post
     */
    public function store(
        int $user_id,
        string $content,
        ?string $title = null,
        ?int $collection_id = null,
        ?string $description = null,
        ?array $tags = null,
    ): Post
    {
        $content = $this->postService->sanitize($content);
        $info = $this->postService->computePostData($content, $title, $description);

        $attributes = array_merge([
            'title' => $title,
            'content' => $content,
            'collection_id' => $collection_id,
            'description' => $description,
            'user_id' => $user_id,
            'order' => $this->repository->getNextOrder($user_id, $collection_id)
        ], $info);

        /**
         * @var Post $post
         */
        $post = Post::query()->create($attributes);

        if ($info['type'] === PostTypeEnum::Link) {
            $this->postImageService->saveImage($info['image_path'], $post);
        }

        if ($tags) {
            $this->postTagService->saveTags($post->getKey(), $tags);
        }

        return $post->load('tags');
    }
}
