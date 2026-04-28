<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionSecurity\SessionPolicy;

final class SecureTransportPolicy
{
    private bool $requireSsl;

    public function __construct(bool $requireSsl = true)
    {
        $this->requireSsl = $requireSsl;
    }

    public function evaluate(array $context) : bool
    {
        if ($this->requireSsl) {
            return ($context['secure'] ?? false) === true;
        }

        return true;
    }
}