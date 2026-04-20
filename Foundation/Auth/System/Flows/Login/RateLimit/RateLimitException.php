<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Login\RateLimit;

use Exception;

/**
 * Exception thrown when login rate limit is exceeded.
 */
class RateLimitException extends Exception
{
    private readonly int $retryAfter;

    public function __construct(
        string $message,
        int    $retryAfter
    )
    {
        $this->retryAfter = $retryAfter;
        parent::__construct(message: $message, code: 429);
    }

    public function getRetryAfter() : int
    {
        return $this->retryAfter;
    }
}
