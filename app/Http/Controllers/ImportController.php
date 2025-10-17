<?php

namespace App\Http\Controllers;

use App\Exceptions\TransactionException;
use App\Http\Requests\ImportStoreRequest;
use App\Models\Collection;
use App\Helpers\NetscapeBookmarkDecoder;
use App\Services\CollectionService;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    public function __construct(
        protected PostService $service,
        protected CollectionService $collectionService,
    )
    {
        //
    }

    /**
     * @param ImportStoreRequest $request
     * @return JsonResponse
     * @throws TransactionException
     */
    public function store(ImportStoreRequest $request): JsonResponse
    {
        $collection = $this->collectionService->firstOrCreate(
            Auth::id(), Collection::IMPORTED_COLLECTION_NAME
        );

        (new NetscapeBookmarkDecoder(Auth::id()))
            ->parseFile($request->file('file'), $collection->id);

        return response()->json('', Response::HTTP_CREATED);
    }

}
