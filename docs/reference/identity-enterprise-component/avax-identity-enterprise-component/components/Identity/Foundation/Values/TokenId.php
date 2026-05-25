<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Values;

use Avax\Components\Identity\Foundation\Failures\InvalidIdentityValue;

final readonly class TokenId
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if ($value === '') {
            throw InvalidIdentityValue::because('Token id cannot be empty.');
        }

        return new self($value);
    }

    public static function random(): self
    {
        return new self(bin2hex(random_bytes(16)));
    }

    public function toString(): string
    {
        return $this->value;
    }
}
