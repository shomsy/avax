<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Limit;

use RuntimeException;

/**
 * Raised when repeated MFA failures are temporarily throttled.
 */
final class MfaAttemptLimitReached extends RuntimeException
{
    private readonly int $retryAfter;

    public function __construct(
        int $retryAfter
    )
    {
        $this->retryAfter = $retryAfter;
        parent::__construct(message: 'MFA verification is temporarily locked.', code: 429);
    }

    public function retryAfter() : int
    {
        return $this->retryAfter;
    }
}
