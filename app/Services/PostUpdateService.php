<?php

namespace App\Services;

use App\Enums\PostTypeEnum;
use App\Models\Collection;
use App\Models\Post;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class PostUpdateService
{
    /**
     * @param PostService $service
     * @param CollectionRepositoryInterface $collectionRepository
     */
    public function __construct(
        protected PostService $service,
        protected CollectionRepositoryInterface $collectionRepository,
    )
    {
        //
    }

    /**
     * @param Post $post
     * @param array $validatedData
     * @param int|null $collectionId
     * @param bool $isUncategorized
     * @param string|null $title
     * @param bool $isArchived
     * @return Post
     */
    public function update(
        Post $post,
        array $validatedData,
        ?int $collectionId = null,
        bool $isUncategorized = false,
        ?string $title = null,
        bool $isArchived = false
    ): Post
    {
        if (!$collectionId && $isUncategorized === false) {
            // request contains no knowledge about a collection
            $validatedData['collection_id'] = $post->collection_id;
        } else {
            $validatedData['collection_id'] = Collection::getCollectionId(
                $collectionId,
                $isUncategorized
            );
        }

        if (!empty($validatedData['collection_id'])) {
            $collection = $this->collectionRepository->getById($validatedData['collection_id']);
            if (!$collection) {
                throw new ModelNotFoundException(
                    'Collection does not exist',
                );
            }
        }

        if (isset($validatedData['content'])) {
            $validatedData['content'] = $this->service->sanitize($validatedData['content']);
            $info = $this->service->computePostData($validatedData['content'], $title);

            if ($validatedData['content'] === $post->content) {
                foreach (['title', 'description', 'image_path'] as $attr) {
                    if (!empty($post->{$attr})) {
                        unset($info[$attr]);
                    }
                }
            }
        } else {
            $info = array();
            $info['type'] = $post->getRawOriginal('type');
        }

        $newValues = array_merge($validatedData, $info);
        $newValues['user_id'] = Auth::id();

        if ($post->collection_id !== $validatedData['collection_id']) {
            // post wants to have a different collection than before
            // compute order in new collection
            $newValues['order'] = Post::query()
                    ->where('collection_id', $validatedData['collection_id'])
                    ->max('order') + 1;

            // reorder old collection
            Post::query()
                ->where('collection_id', $post->collection_id)
                ->where('order', '>', $post->order)
                ->decrement('order');

        } else if (isset($validatedData['order'])) {
            // post wants to be positioned somewhere else
            // staying in the same collection as before
            $newOrder = $validatedData['order'];

            // check authenticity of order
            if (!Post::query()
                ->where('collection_id', $post->collection_id)
                ->where('order', $newOrder)->exists()
            ) {
                $newOrder = Post::query()
                    ->where('collection_id', $post->collection_id)
                    ->max('order');
                $newValues['order'] = $newOrder;
            }

            $oldOrder = $post->order;
            if ($newOrder !== $oldOrder) {
                if ($newOrder > $oldOrder) {
                    Post::query()
                        ->where('collection_id', $post->collection_id)
                        ->whereBetween('order', [$oldOrder + 1, $newOrder])
                        ->decrement('order');
                } else {
                    Post::query()
                        ->where('collection_id', $post->collection_id)
                        ->whereBetween('order', [$newOrder, $oldOrder - 1])
                        ->increment('order');
                }
            }
        }

        $post->update($newValues);

        if ($isArchived) {
            $is_currently_archived = $post->trashed();
            if ($isArchived === true && $is_currently_archived === false) {
                $this->service->delete($post);
                $post = Post::withTrashed()->find($post->id);
            } elseif ($isArchived === false && $is_currently_archived === true) {
                $post = $this->service->restore($post);
            }
        }

        if ($info['type'] === PostTypeEnum::Link && isset($validatedData['content'])) {
            if (empty($post->image_path) || $validatedData['content'] !== $post->content)
                $this->service->saveImage($info['image_path'], $post);
        }

        if (isset($newValues['tags'])) {
            $this->service->saveTags($post->id, $newValues['tags']);
        }

        $post->tags = $post->tags()->get();

        return $post;
    }
}
