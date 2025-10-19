<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * @param string $email
     * @param array $excludeEmails
     * @return Collection
     */
    public function searchByEmail(string $email, array $excludeEmails = []): Collection
    {
        return $this->startCondition()
            ->select('id', 'email')
            ->whereNotIn('email', $excludeEmails)
            ->where('email', 'like', "%{$email}%")
            ->get();
    }
}
