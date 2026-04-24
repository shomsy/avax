<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionSignature;

final class SessionSignature
{
    public function __construct(private string $value) {}

    public function value() : string
    {
        return $this->value;
    }
}