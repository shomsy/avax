<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\User;

use Avax\Components\Application\Validation\System\Capabilities\Standard\Email\ValidateEmail;
use InvalidArgumentException;
use Stringable;

/**
 * Value object representing a user's email within the Auth System.
 */
final readonly class UserEmail implements Stringable
{
    public function __construct(
        public string $value,
    ) {
        if (! (new ValidateEmail)->execute(email: $this->value)) {
            throw new InvalidArgumentException(message: "Invalid email format: {$this->value}");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return strtolower(string: $this->value) === strtolower(string: $other->value);
    }
}
