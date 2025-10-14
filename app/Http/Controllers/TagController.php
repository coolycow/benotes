<?php

namespace App\Http\Controllers;

use App\Exceptions\TransactionException;
use App\Http\Requests\Tag\TagStoreRequest;
use App\Http\Requests\Tag\TagUpdateRequest;
use App\Repositories\Contracts\TagRepositoryInterface;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function __construct(
        protected TagService $service,
        protected TagRepositoryInterface $repository
    )
    {
        //
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->repository->getByUserId(Auth::id())
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $tag = $this->repository->getById($id);;

        if (!$tag) {
            return response()->json('Tag not found', 404);
        }

        $this->authorize('view', $tag);

        return response()->json(['data' => $tag]);
    }

    /**
     * @param TagStoreRequest $request
     * @return JsonResponse
     * @throws TransactionException
     */
    public function store(TagStoreRequest $request)
    {
        $tags = [];

        if ($request->getName()) {
            return response()->json([
                'data' => $this->service->create($request->getName(), Auth::id())
            ], 201);
        } elseif ($request->getTags()) {
            $tags = $this->service->createMany($request->getTags(), Auth::id());
        }

        return count($tags) === 0
            ? response()->json('')
            : response()->json(['data' => $tags], 201);
    }

    /**
     * @param TagUpdateRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(TagUpdateRequest $request, $id): JsonResponse
    {
        if ($this->repository->getByUserIdAndName(Auth::id(), $request->getName())) {
            return response()->json('Tag does already exist', 400);
        }

        $tag = $this->repository->getById($id);
        $this->authorize('update', $tag);

        return response()->json([
            'data' => $tag->update([
                'name' => $request->getName(),
            ])
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $tag = $this->repository->getById($id);

        if (!$tag) {
            return response()->json('Tag not found.', 404);
        }

        $this->authorize('delete', $tag);
        $tag->delete();

        return response()->json('', 204);
    }

}
