<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostService;
use Exception;
use Illuminate\Console\Command;

class ThumbnailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnail:generate {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing thumbnails with chrome';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected PostService $service,
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $post_id = $this->argument('id');

        if (empty($post_id)) {
            $this->info('What post would you like to "improve" ?');
            $post_id = $this->ask('Please specify a post id or type all');
        }

        if ($post_id === 'all') {
            $posts = Post::whereNull('deleted_at')
                ->where('type', Post::POST_TYPE_LINK)
                ->whereNull('image_path');
            $this->info($posts->count() . ' potential posts found. This could take several minutes.');

            foreach ($posts->get() as $post) {
                $this->info('Process post ' . $post->id . '...');
                $this->createThumbnail($post);
            }
        } else {
            /**
             * @var Post $post
             */
            $post = Post::query()->find(intval($post_id));
            $this->createThumbnail($post);
        }
    }

    /**
     * @param Post $post
     * @return void
     * @throws Exception
     */
    private function createThumbnail(Post $post): void
    {
        if ($post->type === Post::POST_TYPE_TEXT) {
            $this->error('Post is not a link and therefore has no thumbnail');
            return;
        }

        if (!@get_headers($post->url)) {
            $this->error('Post has no existing link');
            return;
        }

        $filename = $this->service->generateThumbnailFilename($post->url, $post->id);
        $path = $this->service->getThumbnailPath($filename);
        $this->service->crawlWithChrome($filename, $path, $post->url, $post->id);

        if (file_exists($path)) {
            $post->image_path = $filename;
            $post->save();
        } else {
            $this->error('Thumbnail could not be created');
        }
    }
}
