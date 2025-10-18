<?php

namespace App\Http\Controllers;

use App\Enums\SharePermissionEnum;
use App\Http\Requests\Share\ShareIndexRequest;
use App\Http\Requests\Share\ShareStoreRequest;
use App\Http\Requests\Share\ShareUpdateRequest;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use App\Repositories\Contracts\ShareRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use App\Models\Share;
use Illuminate\Http\Response;

class ShareController extends Controller
{
    public function __construct(
        protected ShareRepositoryInterface $shareRepository,
        protected CollectionRepositoryInterface $collectionRepository
    )
    {
        //
    }

    public function index(ShareIndexRequest $request): JsonResponse
    {
        $shares = !$request->getCollectionId()
            ? $this->shareRepository->getByUserId(Auth::id())
            : $this->shareRepository->getByUserIdAndCollectionId(Auth::id(), $request->collection_id);

        return response()->json(['data' => $shares]);
    }

    /**
     * @param ShareStoreRequest $request
     * @return JsonResponse
     */
    public function store(ShareStoreRequest $request): JsonResponse
    {
        $collection = $this->collectionRepository->getById($request->getCollectionId());

        if (!$collection) {
            throw new ModelNotFoundException(
                'Collection not found',
            );
        }

        $this->authorize('share', $collection);

        if ($this->shareRepository->getByCollectionId($collection->id)) {
            return response()->json('Collection share already exists.', Response::HTTP_BAD_REQUEST);
        }

        $share = Share::query()->create([
            'user_id' => Auth::id(),
            'collection_id' => $request->getCollectionId(),
            'token' => $request->getToken(),
            'is_active' => $request->getIsActive(),
            'permission' => SharePermissionEnum::Read
        ]);

        return response()->json(['data' => $share], 201);
    }

    /**
     * @param ShareUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ShareUpdateRequest $request, int $id): JsonResponse
    {
        $validatedData = $request->validated();

        if (!$share = $this->shareRepository->getById($id)) {
            throw new ModelNotFoundException(
                'Share not found.'
            );
        }

        if ($request->getCollectionId()) {
            $collection = $this->collectionRepository->getById($request->getCollectionId());

            if (!$collection) {
                throw new ModelNotFoundException(
                    'Collection not found',
                );
            }
        } else {
            $collection = $this->collectionRepository->getById($share->collection_id);
        }

        $this->authorize('share', $collection);

        if ($request->getIsActive()) {
            $validatedData['is_active'] = $request->getIsActive();
        }

        $share->update($validatedData);

        return response()->json(['data' => $share]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $share = $this->shareRepository->getById($id);;

        if (!$share) {
            throw new ModelNotFoundException(
                'Share not found.'
            );
        }

        $this->authorize('delete', $share);

        $share->delete();

        return response()->json('Deleted', 204);
    }

    /**
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json(['data' => Auth::guard('share')->user()]);
    }
}
