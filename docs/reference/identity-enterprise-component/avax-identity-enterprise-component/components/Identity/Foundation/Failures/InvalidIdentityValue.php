<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Failures;

use RuntimeException;

final class InvalidIdentityValue extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }
}
