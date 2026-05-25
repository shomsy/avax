<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Values;

use Avax\Components\Identity\Foundation\Failures\InvalidIdentityValue;

final readonly class TokenSecret
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        if (strlen($value) < 32) {
            throw InvalidIdentityValue::because('Token secret must contain at least 32 bytes.');
        }

        return new self($value);
    }

    public function exposeForSigningOnly(): string
    {
        return $this->value;
    }
}
