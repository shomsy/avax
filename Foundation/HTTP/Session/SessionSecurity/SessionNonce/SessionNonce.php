<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionNonce;

final class SessionNonce
{
    public function __construct(private string $value) {}

    public static function generate() : self
    {
        return new self(value: bin2hex(random_bytes(16)));
    }

    public function value() : string
    {
        return $this->value;
    }
}