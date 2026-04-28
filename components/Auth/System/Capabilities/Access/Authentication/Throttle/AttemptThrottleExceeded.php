<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Access\Authentication\Throttle;

use RuntimeException;

/**
 * Raised when an auth-sensitive request is temporarily throttled.
 */
final class AttemptThrottleExceeded extends RuntimeException
{
    public function __construct(
        private readonly int $retryAfter
    )
    {
        parent::__construct(message: 'Too many attempts.', code: 429);
    }

    public function retryAfter() : int
    {
        return $this->retryAfter;
    }
}
