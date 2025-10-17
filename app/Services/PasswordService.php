<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordService
{
    /**
     * @param int $length
     * @return string
     */
    public function generatePassword(int $length = 16): string
    {
        return Str::random($length);
    }

    /**
     * @param string $password
     * @return string
     */
    public function generateHash(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * @param int $length
     * @return string
     */
    public function generateHashedPassword(int $length = 16): string
    {
        return $this->generateHash(
            $this->generatePassword($length)
        );
    }
}
