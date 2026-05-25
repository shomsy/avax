<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Failures;

use RuntimeException;

/**
 * AccessDenied — thrown when tenant membership or resource access is denied.
 *
 * Adapted from the enterprise reference package.
 */
final class AccessDenied extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }
}
