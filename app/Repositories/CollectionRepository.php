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
     * @param int $user_id
     * @param bool $withShared
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUser(int $user_id, bool $withShared = false): \Illuminate\Database\Eloquent\Collection
    {
        return $this->startCondition()
            ->orderBy('name')
            ->get();
    }

    /**
     * @param int $user_id
     * @param bool $withShared
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWithNested(int $user_id, bool $withShared = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->startCondition()
            ->whereNull('parent_id')
            ->with('nested');

        if (!$withShared) {
            $query->where('user_id', $user_id);
        }

        return $query
            ->orderBy('name')
            ->get();
    }
}
