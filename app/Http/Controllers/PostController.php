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
use App\Services\PostUpdateService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function __construct(
        protected PostService $service,
        protected PostUpdateService $updateService,
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

        $post = $this->updateService->update(
            $post,
            $validatedData,
            $request->getCollectionId(),
            $request->getIsUncategorized(),
            $request->getTitle(),
            $request->getIsArchived()
        );

        return response()->json(['data' => $post], Response::HTTP_OK);
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
