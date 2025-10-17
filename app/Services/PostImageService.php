<?php

namespace App\Services;

use App\Jobs\ProcessMissingThumbnail;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\Facades\Image;

readonly class PostImageService
{
    public function __construct(
        protected ThumbnailService $thumbnailService
    )
    {
        //
    }

    /**
     * @param $image_path
     * @param Post $post
     * @return void
     */
    public function saveImage($image_path, Post $post): void
    {
        if (empty($image_path)) {
            ProcessMissingThumbnail::dispatchIf(config('benotes.generate_missing_thumbnails'), $post);
            return;
        }

        if (!config('benotes.use_filesystem')) {
            $post->update(['image_path' => $image_path]);
            return;
        }

        try {
            $image = Image::make($image_path);
        } catch (ImageException $e) {
            Log::notice('Image could not be created: ' . $e->getMessage());
            return;
        }

        if (!isset($image)) {
            return;
        }

        $filename = $this->thumbnailService->generateThumbnailFilename($image_path, $post->id);
        $image = $image->fit(400, 210)->limitColors(255);
        Storage::put('thumbnails/' . $filename, $image->stream());

        $post->update(['image_path' => $filename]);
    }
}
