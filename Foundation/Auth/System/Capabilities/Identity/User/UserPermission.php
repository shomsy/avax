<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\User;

/**
 * Value object representing a permission within the Auth System.
 */
final readonly class UserPermission
{
    public string $value;

    public function __construct(
        string $value
    )
    {
        $this->value = $value;
    }

    public function equals(self $other) : bool
    {
        return $this->value === $other->value;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
