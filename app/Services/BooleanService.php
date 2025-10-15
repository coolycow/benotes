<?php

namespace App\Services;

readonly class BooleanService
{
    /**
     * @param $value
     * @return bool
     */
    public static function boolValue($value = null): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
