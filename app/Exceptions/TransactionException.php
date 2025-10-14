<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class TransactionException extends Exception
{
    /**
     * @param Throwable $e
     * @return static
     */
    public static function error(Throwable $e): static
    {
        return new static($e->getMessage(), $e->getCode(), $e);
    }
}
