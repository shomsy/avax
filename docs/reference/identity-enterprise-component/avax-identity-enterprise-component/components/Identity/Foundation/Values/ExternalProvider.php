<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Foundation\Values;

use Avax\Components\Identity\Foundation\Failures\InvalidIdentityValue;

final readonly class ExternalProvider
{
    private function __construct(private string $name) {}

    public static function named(string $name): self
    {
        $name = mb_strtolower(trim($name));
        if ($name === '') {
            throw InvalidIdentityValue::because('External provider cannot be empty.');
        }

        return new self($name);
    }

    public function name(): string
    {
        return $this->name;
    }
}
