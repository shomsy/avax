<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Failures;

use RuntimeException;

/**
 * TokenRejected — thrown when a token fails signature verification, is expired, or is revoked.
 *
 * Adapted from the enterprise reference package.
 * Used by VerifyAccessToken flow for all token rejection paths.
 */
final class TokenRejected extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }
}
