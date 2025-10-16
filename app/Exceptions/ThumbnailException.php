<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ThumbnailException extends Exception
{
    /**
     * @param Throwable $e
     * @return static
     */
    public static function error(Throwable $e): static
    {
        return new static($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * @param Throwable $e
     * @return static
     */
    public static function domError(Throwable $e): static
    {
        return new static($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * @param int $postId
     * @return static
     */
    public static function postNotFound(int $postId): static
    {
        return new static("Post with id $postId not found", 404);
    }
}
