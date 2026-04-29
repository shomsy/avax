<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Security;

use InvalidArgumentException;
use Random\RandomException;
use Stringable;

/**
 * Immutable session identifier value object.
 */
final readonly class SessionId implements Stringable
{
    public function __construct(
        private string $value
    )
    {
        if ($this->value === '') {
            throw new InvalidArgumentException('Session ID cannot be empty.');
        }
    }

    /**
     * Generate a cryptographically secure session ID.
     *
     * @throws RandomException
     */
    public static function generate() : self
    {
        return new self(bin2hex(random_bytes(32)));
    }

    public function value() : string
    {
        return $this->value;
    }

    public function toString() : string
    {
        return $this->value;
    }

    public function __toString() : string
    {
        return $this->value;
    }

    public function equals(self $other) : bool
    {
        return $this->value === $other->value;
    }
}
