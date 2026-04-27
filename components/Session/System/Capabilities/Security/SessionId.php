<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Capabilities\Security;

final readonly class SessionId
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = trim(string: $value);
    }

    public static function generate(): self
    {
        return new self(value: bin2hex(string: random_bytes(length: 16)));
    }

    public function toString(): string
    {
        return $this->value;
    }
}