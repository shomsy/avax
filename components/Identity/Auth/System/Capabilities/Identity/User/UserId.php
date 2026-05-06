<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\User;

use Stringable;

/**
 * Value object representing a user ID within the Auth System.
 */
final readonly class UserId implements Stringable
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
