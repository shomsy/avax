<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Throttle;

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
        parent::__construct('Too many attempts.', 429);
    }

    public function retryAfter() : int
    {
        return $this->retryAfter;
    }
}
