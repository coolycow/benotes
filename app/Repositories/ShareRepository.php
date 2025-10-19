<?php

namespace App\Repositories;

use App\Models\Share;
use App\Repositories\Contracts\ShareRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ShareRepository extends BaseRepository implements ShareRepositoryInterface
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return Share::class;
    }

    /**
     * @param int $id
     * @return Share|Model|null
     */
    public function getById(int $id): Share|Model|null
    {
        return $this->startCondition()->find($id);
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->startCondition()
            ->where('user_id', $userId)->get();
    }

    /**
     * @param int $collectionId
     * @return Share|Model|null
     */
    public function getByCollectionId(int $collectionId): Share|Model|null
    {
        return $this->startCondition()
            ->where('collection_id', $collectionId)
            ->first();
    }

    /**
     * @param int $userId
     * @param int $collectionId
     * @return Collection
     */
    public function getByUserIdAndCollectionId(int $userId, int $collectionId): Collection
    {
        return $this->startCondition()
            ->where('user_id', $userId)
            ->where('collection_id', $collectionId)
            ->get();
    }

    /**
     * @param int $userId
     * @return Collection
     */
    public function getSharedCollections(int $userId): Collection
    {
        return $this->startCondition()
            ->where('guest_id', $userId)
            ->with(['collection', 'collection.nested'])
            ->get()
            ->map(function ($share) {
                return $share->collection;
            });
    }
}
