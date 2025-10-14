<?php

namespace App\Repositories\Contracts;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Model;

interface CollectionRepositoryInterface
{
    /**
     * @param int $id
     * @return Collection|Model|null
     */
    public function getById(int $id): Collection|Model|null;

    /**
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUserId(int $userId): \Illuminate\Database\Eloquent\Collection;

    /**
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNested(int $userId): \Illuminate\Database\Eloquent\Collection;
}
