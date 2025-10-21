<?php

namespace App\Http\Controllers;

use App\Services\RecycleBinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RecycleBinController extends Controller
{
    public function __construct(
        protected RecycleBinService $service
    )
    {
        //
    }

    /**
     * @return JsonResponse
     */
    public function clear(): JsonResponse
    {
        if ($this->service->clear(Auth::id())) {
            return response()->json('', Response::HTTP_NO_CONTENT);
        } else {
            return response()->json('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
