<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Values;

use InvalidArgumentException;

/**
 * SignedToken — value object wrapping a raw signed token string.
 *
 * Adapted from the enterprise reference package.
 */
final readonly class SignedToken
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException('Signed token cannot be empty.');
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
