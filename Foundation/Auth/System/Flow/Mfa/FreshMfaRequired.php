<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use RuntimeException;

/**
 * Raised when a dangerous action requires recent MFA proof.
 */
final class FreshMfaRequired extends RuntimeException
{
    private readonly int $maxAgeSeconds;

    public function __construct(
        int    $maxAgeSeconds,
        string $message = 'Fresh MFA verification is required.'
    )
    {
        $this->maxAgeSeconds = $maxAgeSeconds;
        parent::__construct(message: $message, code: 403);
    }

    public function maxAgeSeconds() : int
    {
        return $this->maxAgeSeconds;
    }
}
