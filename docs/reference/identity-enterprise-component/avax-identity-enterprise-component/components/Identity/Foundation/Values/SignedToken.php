<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Values;

use Avax\Components\Identity\Foundation\Failures\InvalidIdentityValue;

final readonly class SignedToken
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if ($value === '') {
            throw InvalidIdentityValue::because('Signed token cannot be empty.');
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
