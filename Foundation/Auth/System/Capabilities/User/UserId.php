<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\User;

use Stringable;

/**
 * Value object representing a user ID within the Auth System.
 */
final class UserId implements Stringable
{
    public function __construct(
        public readonly int $value
    ) {}

    public function __toString() : string
    {
        return (string) $this->value;
    }

    public function equals(UserId $other) : bool
    {
        return $this->value === $other->value;
    }
}
