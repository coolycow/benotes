<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface UserRepositoryInterface
{
    /**
     * @param string $email
     * @return User|Model|null
     */
    public function getByEmail(string $email): User|Model|null;

    /**
     * @param int $id
     * @return User|Model|null
     */
    public function getById(int $id): User|Model|null;

    /**
     * @param string $email
     * @param array $excludeEmails
     * @return Collection
     */
    public function searchByEmail(string $email, array $excludeEmails = []): Collection;
}
