<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\User;

/**
 * Value object representing a permission within the Auth System.
 */
final readonly class UserPermission
{
    public function __construct(
        public string $value
    ) {}

    public function equals(UserPermission $other) : bool
    {
        return $this->value === $other->value;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
