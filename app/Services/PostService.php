<?php

namespace App\Services;

use App\Enums\PostTypeEnum;
use App\Enums\UserPermissionEnum;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\PostTagRepositoryInterface;
use DOMDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\Collection;

readonly class PostService
{
    public function __construct(
        protected PostRepositoryInterface $repository,
        protected PostTagRepositoryInterface $postTagRepository,
        protected PostImageService $postImageService,
        protected PostTagService $postTagService,
        protected ThumbnailService $thumbnailService,
        protected ColorService $colorService,
    )
    {
        //
    }

    /**
     * @param int $user_id
     * @param UserPermissionEnum $auth_type
     * @param int|null $collection_id
     * @param bool $is_uncategorized
     * @param int|null $tag_id
     * @param bool $withTags
     * @param string|null $filter
     * @param bool $is_archived
     * @param Post|null $after_post
     * @param int|null $offset
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(
        int $user_id,
        UserPermissionEnum $auth_type,
        ?int $collection_id = null,
        bool $is_uncategorized = false,
        ?int $tag_id = null,
        bool $withTags = false,
        ?string $filter = null,
        bool $is_archived = false,
        Post $after_post = null,
        ?int $offset = null,
        ?int $limit = 50
    ): \Illuminate\Database\Eloquent\Collection
    {
        $posts = Post::query();

        if ($withTags) {
            $posts = $posts->with('tags:id,name');
        }

        if ($tag_id) {
            $post_ids = $this->postTagRepository->getByTagId($tag_id)->pluck('post_id')->toArray();
            $posts = $posts->whereIn('id', $post_ids);
        }

        if ($auth_type === UserPermissionEnum::Api) {
            if ($collection_id > 0 && $filter !== '') {
                $collection_ids = Collection::with('children')
                    ->where('parent_id', $collection_id)
                    ->pluck('id')->all();
                $collection_ids[] = $collection_id;
                $posts = $posts
                    ->whereIn('collection_id', $collection_ids)
                    ->where('user_id', '=', Auth::user()->id);
            } elseif ($collection_id > 0 || $is_uncategorized === true) {
                $collection_id = Collection::getCollectionId(
                    $collection_id,
                    $is_uncategorized
                );
                $posts = $posts->where([
                    ['collection_id', '=', $collection_id],
                    ['user_id', '=', $user_id]
                ]);
            } else {
                $posts = $posts->where('user_id', Auth::user()->id);
            }
        } elseif ($auth_type === UserPermissionEnum::Share) {
            $share = Auth::guard('share')->user();
            $posts = $posts->where([
                'collection_id' => $share->collection_id,
                'user_id' => $share->user_id
            ]);
        }

        if ($filter !== '' && $auth_type === UserPermissionEnum::Api) {
            $filter = strtolower($filter);
            $posts = $posts->where(function ($query) use ($filter) {
                $query
                    ->whereRaw('LOWER(title) LIKE ?', "%{$filter}%")
                    ->orWhereRaw('LOWER(content) LIKE ?', "%{$filter}%");
            });
        }

        if ($after_post !== null) {
            $posts = $posts->where('order', '<', $after_post->order);
        } elseif ($offset) {
            $posts = $posts->offset($offset);
        }

        if ($limit) {
            $posts = $posts->limit($limit);
        }

        if ($is_archived) {
            return $posts
                ->onlyTrashed()
                ->orderBy('deleted_at', 'desc')->get();
        }

        return $posts->orderBy('order', 'desc')->get();
    }

    /**
     * @param int $user_id
     * @param string $content
     * @param string|null $title
     * @param int|null $collection_id
     * @param string|null $description
     * @param array|null $tags
     * @return Post
     */
    public function store(
        int $user_id,
        string $content,
        ?string $title = null,
        ?int $collection_id = null,
        ?string $description = null,
        ?array $tags = null,
        ): Post
    {
        $content = $this->sanitize($content);
        $info = $this->computePostData($content, $title, $description);

        $attributes = array_merge([
            'title' => $title,
            'content' => $content,
            'collection_id' => $collection_id,
            'description' => $description,
            'user_id' => $user_id,
            'order' => $this->repository->getNextOrder($user_id, $collection_id)
        ], $info);

        /**
         * @var Post $post
         */
        $post = Post::query()->create($attributes);

        if ($info['type'] === PostTypeEnum::Link) {
            $this->postImageService->saveImage($info['image_path'], $post);
        }

        if ($tags) {
            $this->postTagService->saveTags($post->getKey(), $tags);
        }

        /**
         * Важно делать именно так, чтобы правильно работал трансфер!!!
         * @see resources/js/store/modules/post.js
         */
        $post->tags = $post->tags()->get();
        return $post;
    }

    /**
     * @param Post $post
     * @return void
     */
    public function delete(Post $post): void
    {
        Post::query()->where('collection_id', $post->collection_id)
            ->where('order', '>', $post->order)
            ->decrement('order');

        $post->delete();
    }

    /**
     * @param Post $post
     * @return Post
     */
    public function restore(Post $post): Post
    {
        $maxOrder = Post::query()->where('collection_id', $post->collection_id)->max('order');

        if ($post->order <= $maxOrder) {
            Post::query()->where('collection_id', $post->collection_id)
                ->where('order', '>=', $post->order)
                ->increment('order');
        } else {
            $post->order = $maxOrder + 1;
        }

        $post->restore();

        return $post;
    }

    /**
     * @param string $content
     * @param string|null $title
     * @param string|null $description
     * @return array
     */
    public function computePostData(string $content, string $title = null, string $description = null): array
    {
        // more explicit: https?(:\/\/)((\w|-)+\.)+(\w+)(\/\w+)*(\?)?(\w=\w+)?(&\w=\w+)*
        preg_match_all('/(https?:\/\/)((\S+?\.|localhost:)\S+?)(?=\s|<|"|$)/', $content, $matches);
        $matches = $matches[0];
        $info = null;

        if (count($matches) > 0) {
            $info = $this->getInfo($matches[0]);
        }

        if (!empty($title)) {
            unset($info['title']);
        }
        if (!empty($description)) {
            unset($info['description']);
        }

        $stripped_content = strip_tags($content);

        if (empty($matches)) {
            $info['type'] = PostTypeEnum::Text;
        } elseif (strlen($stripped_content) > strlen($matches[0])) { // contains more than just a link
            $info['type'] = PostTypeEnum::Text;
        } elseif ($stripped_content != $matches[0]) {
            $info['type'] = PostTypeEnum::Text;
        } else {
            $info['type'] = PostTypeEnum::Link;
        }

        return $info;
    }

    /**
     * @param string $str
     * @return string
     */
    public function sanitize(string $str): string
    {
        return strip_tags($str, '<a><strong><b><em><i><s><p><h1><h2><h3><h4><h5>' .
            '<pre><br><hr><blockquote><ul><li><ol><code><img><unfurling-link>');
    }

    /**
     * @param string $url
     * @param bool $act_as_bot
     * @return array
     */
    public function getInfo(string $url, bool $act_as_bot = false): array
    {
        $base_url = parse_url($url);
        $base_url = $base_url['scheme'] . '://' . $base_url['host'];

        $useragent = $act_as_bot
            ? 'Googlebot/2.1 (+http://www.google.com/bot.html)'
            : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/html'));
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, config('benotes.curl_timeout'));
        $html = curl_exec($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if (empty($html) && !$act_as_bot) {
            return $this->getInfo($url, true);
        }

        if (empty($html) || !Str::contains($content_type, 'text/html')) {
            return [
                'url'         => substr($url, 0, 512),
                'base_url'    => substr($base_url, 0, 255),
                'title'       => substr($url, 0, 255),
                'description' => null,
                'color'       => null,
                'image_path'  => null,
            ];
        }

        $document = new DOMDocument();
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        @$document->loadHTML($html);
        $titles = $document->getElementsByTagName('title');
        if (count($titles) > 0) {
            $title = trim($titles->item(0)->nodeValue);
        } else {
            $title = $base_url;
        }
        $metas = $document->getElementsByTagName('meta');

        for ($i = 0; $i < $metas->length; $i++) {
            $meta = $metas->item($i);
            if ($meta->getAttribute('name') === 'description') {
                $description = trim($meta->getAttribute('content'));
            } elseif ($meta->getAttribute('name') === 'theme-color') {
                $color = $meta->getAttribute('content');
            } elseif ($meta->getAttribute('property') === 'og:image') {
                if ($meta->getAttribute('content') != '') {
                    $image_path = $meta->getAttribute('content');
                    if (Str::startsWith($image_path, parse_url($image_path)['path'])) {
                        $image_path = $this->composeImagePath($image_path, $base_url, $url);
                    }
                }
            }
        }

        if (empty($color)) {
            $color = $this->colorService->getDominantColor($base_url);
        }

        if (empty($description) && empty($image_path) & !$act_as_bot) {
            // try again with bot as useragent
            return $this->getInfo($url, true);
        }

        return [
            'url'         => substr($url, 0, 512),
            'base_url'    => substr($base_url, 0, 255),
            'title'       => substr($title, 0, 255),
            'description' => $description ?? null,
            'color'       => $color ?? null,
            'image_path'  => $image_path ?? null,
        ];
    }

    /**
     * @param string $image_path
     * @param string $base_url
     * @param string $url
     * @return string
     */
    private function composeImagePath(string $image_path, string $base_url, string $url): string
    {
        if (str_starts_with($image_path, './')) {
            return $url . Str::replaceFirst('./', '/', $image_path);
        } else if (str_starts_with($image_path, '../')) {
            return preg_replace('/\/\w+\/$/', '/', $url) .
                Str::replaceFirst('../', '', $image_path);
        }
        return $base_url . $image_path;
    }
}
