<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionSecurity\SessionNonce;

use Random\RandomException;

final class SessionNonce
{
    public function __construct(private string $value) {}

    /**
     * @throws RandomException
     */
    public static function generate() : self
    {
        return new self(value: bin2hex(random_bytes(16)));
    }

    public function value() : string
    {
        return $this->value;
    }
}