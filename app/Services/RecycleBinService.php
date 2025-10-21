<?php

namespace App\Services;

use App\Models\Post;

readonly class RecycleBinService
{
    public function __construct(

    )
    {
        //
    }

    /**
     * @param int $userId
     * @return bool|int|null
     */
    public function clear(int $userId): bool|int|null
    {
        return Post::onlyTrashed()
            ->where('user_id', $userId)
            ->forceDelete();
    }
}
