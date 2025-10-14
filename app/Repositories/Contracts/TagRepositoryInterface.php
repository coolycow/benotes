<?php

namespace App\Repositories\Contracts;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface TagRepositoryInterface
{
    /**
     * @param int $id
     * @return Tag|Model|null
     */
    public function getById(int $id): Tag|Model|null;

    /**
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection;

    /**
     * @param int $userId
     * @param string $name
     * @return Tag|Model|null
     */
    public function getByUserIdAndName(int $userId, string $name): Tag|Model|null;
}
