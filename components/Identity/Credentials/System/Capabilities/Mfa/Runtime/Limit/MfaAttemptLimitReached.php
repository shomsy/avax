<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit;

use RuntimeException;

/**
 * Raised when repeated MFA failures are temporarily throttled.
 */
final class MfaAttemptLimitReached extends RuntimeException
{
    public function __construct(
        private readonly int $retryAfter,
    ) {
        parent::__construct(message: 'MFA verification is temporarily locked.', code: 429);
    }

    public function retryAfter(): int
    {
        return $this->retryAfter;
    }
}
