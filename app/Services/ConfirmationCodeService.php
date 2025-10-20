<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Random\RandomException;

readonly class ConfirmationCodeService
{
    /**
     * @param int $min
     * @param int $max
     * @param int $length
     * @return string
     * @throws RandomException
     */
    public function generate(int $min = 100000, int $max = 999999, int $length = 6): string
    {
        if (App::isLocal() && config('benotes.local_secure_code')) {
            return config('benotes.local_secure_code');
        }

        return str_pad(random_int($min, $max), $length, '0', STR_PAD_LEFT);
    }
}
