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
     * @param int $user_id
     * @param bool $withShared
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUser(int $user_id, bool $withShared = false): \Illuminate\Database\Eloquent\Collection;

    /**
     * @param int $user_id
     * @param bool $withShared
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWithNested(int $user_id, bool $withShared = false): \Illuminate\Database\Eloquent\Collection;
}
