<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return User::class;
    }

    /**
     * @param string $email
     * @return User|Model|null
     */
    public function getByEmail(string $email): User|Model|null
    {
        return $this->startCondition()->where('email', $email)->first();
    }

    /**
     * @param int $id
     * @return User|Model|null
     */
    public function getById(int $id): User|Model|null
    {
        return $this->startCondition()->find($id);
    }
}
