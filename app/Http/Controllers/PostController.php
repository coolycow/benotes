<?php

namespace App\Http\Controllers;

use App\Enums\UserPermissionEnum;
use App\Http\Requests\Post\PostDeleteRequest;
use App\Http\Requests\Post\PostIndexRequest;
use App\Http\Requests\Post\PostShowRequest;
use App\Http\Requests\Post\PostStoreRequest;
use App\Http\Requests\Post\PostUpdateRequest;
use App\Http\Requests\Post\PostUrlInfoRequest;
use App\Models\Collection;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\TagRepositoryInterface;
use App\Services\PostService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function __construct(
        protected PostService $service,
        protected PostRepositoryInterface $repository,
        protected CollectionRepositoryInterface $collectionRepository,
        protected TagRepositoryInterface $tagRepository,
    )
    {
        //
    }

    /**
     * @param PostIndexRequest $request
     * @return JsonResponse
     */
    public function index(PostIndexRequest $request): JsonResponse
    {
        $auth_type = User::getAuthenticationType();

        if ($auth_type === UserPermissionEnum::Unauthorized) {
            return response()->json('', Response::HTTP_UNAUTHORIZED);
        }

        $after_post = null;
        if ($request->getAfterId()) {
            if (!$request->getCollectionId() && !$request->getIsUncategorized()) {
                return response()->json(
                    'collection_id or is_uncategorized is required',
                    Response::HTTP_BAD_REQUEST
                );
            }
            $after_post = $this->repository->getById($request->getAfterId());
            if ($after_post == null) {
                return response()->json('after_id does not exist', Response::HTTP_NOT_FOUND);
            }

            $this->authorize('view', $after_post);
            $collection_id = Collection::getCollectionId(
                $request->getCollectionId(),
                $request->getIsUncategorized()
            );

            if ($after_post->collection_id !== $collection_id) {
                return response()->json('Wrong collection', Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->getTagId()) {
            if (!$this->tagRepository->getById($request->getTagId())) {
                return response()->json('Tag does not exist', Response::HTTP_BAD_REQUEST);
            }
        }

        $posts = $this->service->all(
            Auth::id(),
            $request->getCollectionId(),
            $request->getIsUncategorized(),
            $request->getTagId(),
            $request->getWithTags(),
            $request->getFilter(),
            $auth_type,
            $request->getIsArchived(),
            $after_post,
            $request->getOffset(),
            $request->getLimit(),
        );

        return response()->json(['data' => $posts]);
    }

    /**
     * @param int $id
     * @param PostShowRequest $request
     * @return JsonResponse
     */
    public function show(int $id, PostShowRequest $request): JsonResponse
    {
        $post = $this->repository->getById($id, $request->getWithTags());

        if ($post === null) {
            return response()->json('Post does not exist', Response::HTTP_NOT_FOUND);
        }

        $this->authorize('view', $post);

        return response()->json(['data' => $post], Response::HTTP_OK);
    }

    /**
     * @param PostStoreRequest $request
     * @return JsonResponse
     */
    public function store(PostStoreRequest $request): JsonResponse
    {
        if ($request->getCollectionId()) {
            $collection = $this->collectionRepository->getById($request->getCollectionId());

            if (!$collection) {
                throw new ModelNotFoundException(
                    'Collection does not exist',
                );
            }

            if (Auth::id() !== $collection->user_id) {
                return response()->json('Not authorized', Response::HTTP_FORBIDDEN);
            }
        }

        $post = $this->service->store(
            Auth::id(),
            $request->getPostContent(),
            $request->getTitle(),
            $request->getCollectionId(),
            $request->getDescription(),
            $request->getTags(),
        );

        return response()->json(['data' => $post], Response::HTTP_CREATED);
    }

    /**
     * @param PostUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(PostUpdateRequest $request, int $id)
    {
        $validatedData = $request->validated();

        $post = Post::withTrashed()->find($id);
        if (!$post) {
            return response()->json('Post not found.', Response::HTTP_NOT_FOUND);
        }

        $this->authorize('update', $post);

        if ($request->getCollectionId() && $request->getIsUncategorized() === false) {
            // request contains no knowledge about a collection
            $validatedData['collection_id'] = $post->collection_id;
        } else {
            $validatedData['collection_id'] = Collection::getCollectionId(
                $request->getCollectionId(),
                $request->getIsUncategorized()
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
            $info = $this->service->computePostData($validatedData['content'], $request->getTitle());

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

        if ($request->getIsArchived()) {
            $is_currently_archived = $post->trashed();
            if ($request->getIsArchived() === true && $is_currently_archived === false) {
                $this->service->delete($post);
                $post = Post::withTrashed()->find($post->id);
            } else if ($request->getIsArchived() === false && $is_currently_archived === true) {
                $post = $this->service->restore($post);
            }
        }

        if ($info['type'] === Post::POST_TYPE_LINK && isset($validatedData['content'])) {
            if (empty($post->image_path) || $validatedData['content'] !== $post->content)
                $this->service->saveImage($info['image_path'], $post);
        }

        if (isset($newValues['tags'])) {
            $this->service->saveTags($post->id, $newValues['tags']);
        }

        return response()->json(['data' => $post->refresh()], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @param PostDeleteRequest $request
     * @return JsonResponse
     */
    public function destroy(int $id, PostDeleteRequest $request): JsonResponse
    {
        $post = $this->repository->getById($id);

        if (!$post) {
            return response()->json('Post not found.', Response::HTTP_NOT_FOUND);
        }

        $this->authorize('delete', $post);

        $this->service->delete($post);

        return response()->json('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param PostUrlInfoRequest $request
     * @return JsonResponse
     */
    public function getUrlInfo(PostUrlInfoRequest $request): JsonResponse
    {
        return response()->json($this->service->getInfo($request->getUrl()));
    }
}
