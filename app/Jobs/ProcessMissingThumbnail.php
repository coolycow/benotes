<?php

namespace App\Jobs;

use App\Enums\PostTypeEnum;
use App\Models\Post;
use App\Services\PostService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ProcessMissingThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param Post $post
     * @param PostService $service
     */
    public function __construct(
        private Post $post,
        private PostService $service
    )
    {
        $this->post = $post->withoutRelations();
        $this->service = app(PostService::class);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->post->id))->releaseAfter(60)];
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        if ($this->post->type === PostTypeEnum::Text) {
            return;
        }
        if (!empty($this->post->image_path)) {
            return;
        }
        if (!empty($this->post->deleted_at)) {
            return;
        }
        if (@get_headers($this->post->url) == false) {
            return;
        }

        $filename = $this->service->generateThumbnailFilename($this->post->url, $this->post->id);
        $path = $this->service->getThumbnailPath($filename);
        $this->service->crawlWithChrome($filename, $path, $this->post->url, $this->post->id);

        if (file_exists($path)) {
            $this->post->image_path = $filename;
            $this->post->save();
        }
    }
}
