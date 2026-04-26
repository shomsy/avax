<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\User;

use Stringable;

/**
 * Value object representing a permission within the Auth System.
 */
final readonly class UserPermission implements Stringable
{
    public function __construct(public string $value) {}

    public function equals(self $other) : bool
    {
        return $this->value === $other->value;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
