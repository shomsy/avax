<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Values;

use Avax\Components\Identity\Foundation\Failures\InvalidIdentityValue;

final readonly class PasswordHash
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        if (trim($value) === '') {
            throw InvalidIdentityValue::because('Password hash cannot be empty.');
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
