<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Values;

use Avax\Components\Identity\Foundation\Failures\InvalidIdentityValue;

final readonly class LoginName
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $value = mb_strtolower(trim($value));
        if ($value === '') {
            throw InvalidIdentityValue::because('Login name cannot be empty.');
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
