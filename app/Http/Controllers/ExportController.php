<?php

namespace App\Http\Controllers;

use App\Helpers\NetscapeBookmarkEncoder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    /**
     * @return JsonResponse|BinaryFileResponse
     */
    public function index(): JsonResponse|BinaryFileResponse
    {

        if (!is_writable(config('benotes.temporary_directory'))) {
            return response()->json('Missing write permission', Response::HTTP_BAD_GATEWAY);
        }

        $tempDirectory = (new TemporaryDirectory(config('benotes.temporary_directory')))
            ->name('export')
            ->force()
            ->create()
            ->empty();

        $path = $tempDirectory->path('export.html');
        $encoder = new NetscapeBookmarkEncoder(Auth::id());
        $encoder->encodeToFile($path);

        return response()->download($path)->deleteFileAfterSend();
    }

}
