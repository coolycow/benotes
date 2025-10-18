<?php

namespace App\Http\Controllers;

use App\Exceptions\TransactionException;
use App\Http\Requests\Collection\CollectionDeleteRequest;
use App\Http\Requests\Collection\CollectionIndexRequest;
use App\Http\Requests\Collection\CollectionStoreRequest;
use App\Http\Requests\Collection\CollectionUpdateRequest;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use App\Services\CollectionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CollectionController extends Controller
{
    public function __construct(
        protected CollectionService $service,
        protected CollectionRepositoryInterface $repository
    )
    {
        //
    }

    /**
     * @param CollectionIndexRequest $request
     * @return JsonResponse
     */
    public function index(CollectionIndexRequest $request): JsonResponse
    {
        $collections = $request->getNested()
            ? $this->repository->getWithNested(Auth::id())
            : $this->repository->getByUserId(Auth::id());

        return response()->json(['data' => $collections]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $collection = $this->repository->getById($id);;

        if (!$collection) {
            throw new ModelNotFoundException(
                'Collection not found',
            );
        }

        $this->authorize('view', $collection);

        return response()->json(['data' => $collection]);
    }

    /**
     * @param CollectionStoreRequest $request
     * @return JsonResponse
     */
    public function store(CollectionStoreRequest $request): JsonResponse
    {
        $parent_id = null;

        if ($request->getParentId()) {
            $collection = $this->repository->getById($request->getParentId());;

            if (!$collection) {
                throw new ModelNotFoundException(
                    'Collection not found',
                );
            }

            $this->authorize('inherit', $collection);
            $parent_id = $collection->id;
        }

        $collection = $this->service->store(
            Auth::id(),
            $request->getName(),
            $parent_id,
            $request->getIconId()
        );

        return response()->json(['data' => $collection], 201);
    }

    /**
     * @param CollectionUpdateRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(CollectionUpdateRequest $request, $id): JsonResponse
    {
        $collection = $this->repository->getById($id);

        if (!$collection) {
            throw new ModelNotFoundException(
                'Collection not found'
            );
        }

        $this->authorize('update', $collection);

        if ($request->getParentId()) {
            if ($collection->getKey() === $request->getParentId())
                return response()->json('Not possible', Response::HTTP_BAD_REQUEST);

            if (!$parent_collection = $this->repository->getById($request->getParentId())) {
                return response()->json('Parent collection not found', Response::HTTP_NOT_FOUND);
            }

            $this->authorize('inherit', $parent_collection);
        }

        $collection = $this->service->update(
            $id,
            $request->getName(),
            $request->getIsRoot(),
            $request->getParentId(),
            $request->getIconId()
        );

        return response()->json(['data' => $collection]);
    }

    /**
     * @param CollectionDeleteRequest $request
     * @param $id
     * @return JsonResponse
     * @throws TransactionException
     */
    public function destroy(CollectionDeleteRequest $request, $id): JsonResponse
    {
        $collection = $this->repository->getById($id);;

        if (!$collection) {
            throw new ModelNotFoundException(
                'Collection not found',
            );
        }

        $this->authorize('delete', $collection);

        $this->service->delete($id, $request->getNested(), Auth::id());

        return response()->json('', 204);
    }
}
