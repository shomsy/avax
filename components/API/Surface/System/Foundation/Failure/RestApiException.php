<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Foundation\Failure;

use RuntimeException;

class RestApiException extends RuntimeException
{
    public static function invalidRequest(string $message) : self
    {
        return new self($message, 400);
    }

    public static function notFound(string $resource) : self
    {
        return new self("{$resource} not found.", 404);
    }

    public static function unauthorized(string $message = 'Unauthorized') : self
    {
        return new self($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden') : self
    {
        return new self($message, 403);
    }
}
