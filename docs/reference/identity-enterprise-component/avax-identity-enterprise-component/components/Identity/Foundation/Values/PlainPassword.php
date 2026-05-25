<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Values;

use Avax\Components\Identity\Foundation\Failures\InvalidIdentityValue;

final readonly class PlainPassword
{
    private function __construct(private string $value) {}

    public static function fromSensitiveString(string $value): self
    {
        if ($value === '') {
            throw InvalidIdentityValue::because('Password cannot be empty.');
        }

        return new self($value);
    }

    public function exposeForHashingOnly(): string
    {
        return $this->value;
    }
}
