<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity;

final class SessionSecurity
{
    public function verifyPolicy(string $action, array $context = []) : bool
    {
        throw new \RuntimeException('SessionSecurity not implemented - placeholder for refactor');
    }
}
