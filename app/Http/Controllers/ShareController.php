<?php

namespace App\Http\Controllers;

use App\Exceptions\TransactionException;
use App\Http\Requests\Share\ShareIndexRequest;
use App\Http\Requests\Share\ShareStoreRequest;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use App\Repositories\Contracts\ShareRepositoryInterface;
use App\Services\ShareService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ShareController extends Controller
{
    public function __construct(
        protected ShareService $shareService,
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

        return response()->json([
            'data' => $shares->map(function ($share) {
                return $share->only(['id', 'user_id', 'collection_id', 'guest_id', 'permission', 'email']);
            })
        ]);
    }

    /**
     * @param ShareStoreRequest $request
     * @return JsonResponse
     * @throws TransactionException
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

        $shares = $this->shareService->updateOrCreateMany($collection, $request->getGuests());

        return response()->json([
            'data' => $shares->map(function ($share) {
                return $share->only(['id', 'user_id', 'collection_id', 'guest_id', 'permission', 'email']);
            })
        ], 201);
    }

    /**
     * @return JsonResponse
     */
    public function sharedCollections(): JsonResponse
    {
        $sharedCollections = $this->shareRepository->getSharedCollections(Auth::id());

        return response()->json(['data' => $sharedCollections]);
    }
}
