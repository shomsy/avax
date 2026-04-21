<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\User;

use InvalidArgumentException;
use Stringable;

/**
 * Value object representing a user's email within the Auth System.
 */
final readonly class UserEmail implements Stringable
{
    public string $value;

    public function __construct(
        string $value
    )
    {
        $this->value = $value;
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(message: "Invalid email format: {$value}");
        }
    }

    public function __toString() : string
    {
        return $this->value;
    }

    public function equals(self $other) : bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }
}
