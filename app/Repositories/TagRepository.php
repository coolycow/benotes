<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return Tag::class;
    }

    /**
     * @param int $id
     * @return Tag|Model|null
     */
    public function getById(int $id): Tag|Model|null
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
            ->where('user_id', $userId)
            ->orderBy('name')->get();
    }

    /**
     * @param int $userId
     * @param string $name
     * @return Tag|Model|null
     */
    public function getByUserIdAndName(int $userId, string $name): Tag|Model|null
    {
        return $this->startCondition()
            ->where('name', $name)
            ->where('user_id', $userId)->first();
    }
}
