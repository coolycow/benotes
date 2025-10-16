<?php

namespace App\Services;

use App\Exceptions\ThumbnailException;
use App\Repositories\Contracts\PostRepositoryInterface;
use Exception;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

readonly class ThumbnailService
{
    public function __construct(
        protected PostRepositoryInterface $postRepository,
    )
    {
        //
    }

    /**
     * @param $name
     * @param $id
     * @return string
     */
    public function generateThumbnailFilename($name, $id): string
    {
        return 'thumbnail_' . md5($name) . '_' . $id . '.jpg';
    }

    /**
     * @param $filename
     * @return string
     */
    public function getThumbnailPath($filename): string
    {
        return storage_path('app/public/thumbnails/' . $filename);
    }

    /**
     * @param string $filename
     * @param string $path
     * @param string $url
     * @param int $postId
     * @return void
     * @throws Exception
     */
    public function crawlWithChrome(string $filename, string $path, string $url, int $postId): void
    {
        $imagePath = $path;
        $width = 400;
        $height = 210;
        // use googlebot in order to avoid, among others, cookie consensus banners
        $useragent = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
        $browser = config('benotes.browser') === 'chromium' ? 'chromium-browser' : 'google-chrome';

        $factory = new BrowserFactory($browser);
        $browser = $factory->createBrowser([
            'noSandbox'   => true,
            'keepAlive'   => true,
            'userAgent'   => $useragent,
            'customFlags' => [
                '--disable-dev-shm-usage',
                '--disable-gpu'
            ],
            'debugLogger' => config('app.debug') ? storage_path('logs/browser.log') : null
        ]);

        try {
            $page = $browser->createPage();
            $navigation = $page->navigate($url);
            $navigation->waitForNavigation(Page::NETWORK_IDLE, 15000);

            // Явное ожидание появления тега title в DOM с таймаутом
            $title = null;
            $maxAttempts = 5;
            $attempt = 0;
            $interval = 500; // мс

            while ($attempt < $maxAttempts) {
                try {
                    $titleEl = $page->dom()->querySelector('title');
                    if ($titleEl !== null) {
                        $title = $titleEl->getText();
                        break;
                    }
                } catch (Exception $e) {
                    Log::warning('DOM node error: ' . $e->getMessage());
                }
                usleep($interval * 1000);
                $attempt++;
            }

            if ($title === null) {
                $title = $page->evaluate('document.title')->getReturnValue();
            }

            // Получаем description и og:image
            $descriptionEl = $page->dom()->querySelector('head meta[name=description]');
            $description = $descriptionEl?->getAttribute('content');
            $imageEl = $page->dom()->querySelector('head meta[property=\'og:image\']');
            $imagePathOG = $imageEl?->getAttribute('content');

            if (!$post = $this->postRepository->getById($postId)) {
                throw ThumbnailException::postNotFound($postId);
            }

            if (!empty($title) && $title !== $post->title || !empty($description) && empty($post->description)) {
                if (!empty($title) && $title !== $post->title) {
                    $post->title = $title;
                }

                if (!empty($description) && empty($post->description)) {
                    $post->description = $description;
                }

                $post->save();
            }

            if (!empty($imagePathOG)) {
                // if crawling the website with chromium reveals an already
                // existing thumbnail, use it instead
                $imagePath = $imagePathOG;
            } else {
                $page->screenshot()->saveToFile($imagePath);
            }

            // temporally store the image
            $image = Image::make($imagePath);
            if (!$image) {
                return;
            }
            $image = $image->fit($width, $height);
            Storage::put('thumbnails/' . $filename, $image->stream());
        } catch (Exception $e) {
            throw ThumbnailException::error($e);
        } finally {
            $browser->close();
        }
    }
}
