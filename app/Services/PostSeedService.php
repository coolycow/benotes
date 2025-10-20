<?php

namespace App\Services;

use App\Enums\PostTypeEnum;
use App\Models\Post;

readonly class PostSeedService
{
    /**
     * @param int $user_id
     * @param null $collection_id
     * @return void
     */
    public function seedIntroData(int $user_id, $collection_id = null): void
    {
        $i = 1;

        Post::query()->create([
            'title'         => 'GitHub - coolycow/benotes-next: An open source self hosted web app for your notes and bookmarks.',
            'content'       => 'https://github.com/coolycow/benotes-next',
            'type'          => PostTypeEnum::Link,
            'url'           => 'https://github.com/coolycow/benotes-next',
            'color'         => '#1e2327',
            'image_path'    => 'https://opengraph.githubassets.com/9c1b74a8cc5eeee5c5c9f62701c42e1356595422d840d2e209bceb836deb5ffb/coolycow/benotes',
            'base_url'      => 'https://github.com',
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'order'         => $i++,
        ]);

        Post::query()->create([
            'title'         => 'Also...',
            'content'       => '<p>you can save (or paste, if your browser allows it) bookmarks ! ➡️</p>',
            'type'          => PostTypeEnum::Text,
            'description'   => null,
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'order'         => $i++
        ]);

        Post::query()->create([
            'title'         => 'Text Post 📝',
            'content'       => '<p>This post demonstrates different features of Benotes. <br>' .
                'You can write <strong>bold</strong>, ' .
                '<em>italic</em> or <strong>combine <em>them</em></strong><em>.</em></p>' .
                '<blockquote><p>Blockquotes are also a thing</p></blockquote>' .
                '<hr> <p>You can also use Markdown in order to type faster. <br>' .
                'If you are not familiar with the syntax have a look at</p>' .
                '<unfurling-link href="https://www.markdownguide.org/cheat-sheet/" ' .
                'data-title="Markdown Cheat Sheet | Markdown Guide"></unfurling-link>',
            'type'          => PostTypeEnum::Text,
            'description'   => null,
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'order'         => $i++
        ]);
    }
}
