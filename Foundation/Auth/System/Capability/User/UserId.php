<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\User;

use Stringable;

/**
 * Value object representing a user ID within the Auth System.
 */
final readonly class UserId implements Stringable
{
    public function __construct(
        public int $value
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
