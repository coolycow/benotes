<?php

namespace App\Services;

use Random\RandomException;

class ConfirmationCodeService
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
        return str_pad(random_int($min, $max), $length, '0', STR_PAD_LEFT);
    }
}
