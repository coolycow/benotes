<?php

namespace App\Repositories\Contracts;

use App\Models\Share;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ShareRepositoryInterface
{
    /**
     * @param int $id
     * @return Share|Model|null
     */
    public function getById(int $id): Share|Model|null;

    /**
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection;

    /**
     * @param int $collectionId
     * @return Share|Model|null
     */
    public function getByCollectionId(int $collectionId): Share|Model|null;

    /**
     * @param int $userId
     * @param int $collectionId
     * @return Share|Model|null
     */
    public function getByUserIdAndCollectionId(int $userId, int $collectionId): Share|Model|null;
}
