<?php

namespace App\Repositories;

use App\Models\Collection;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CollectionRepository extends BaseRepository implements CollectionRepositoryInterface
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return Collection::class;
    }

    /**
     * @param int $id
     * @return Collection|Model|null
     */
    public function getById(int $id): Collection|Model|null
    {
        return $this->startCondition()->find($id);
    }

    /**
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUserId(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->startCondition()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWithNested(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->startCondition()
            ->where('user_id', $userId)
            ->whereNull('parent_id')
            ->with('nested')
            ->orderBy('name')
            ->get();
    }
}
