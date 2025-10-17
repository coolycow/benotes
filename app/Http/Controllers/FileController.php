<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    /**
     * @param FileStoreRequest $request
     * @return JsonResponse
     */
    public function store(FileStoreRequest $request): JsonResponse
    {
        $path = $request->file('file')->store('attachments');

        Image::make(Storage::path($path))
            ->resize(1600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->interlace()
            ->save();

        return response()->json([
            'data' => [
                'path' => Storage::url($path)
            ]
        ], Response::HTTP_CREATED);
    }

}
