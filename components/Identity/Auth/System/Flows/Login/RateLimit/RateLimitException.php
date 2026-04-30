<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Login\RateLimit;

use Exception;

/**
 * Exception thrown when login rate limit is exceeded.
 */
final class RateLimitException extends Exception
{
    public function __construct(
        string               $message,
        private readonly int $retryAfter,
    )
    {
        parent::__construct(message: $message, code: 429);
    }

    public function getRetryAfter() : int
    {
        return $this->retryAfter;
    }
}
