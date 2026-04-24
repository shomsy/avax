<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionPolicy;

final class IdleTimeoutPolicy
{
    private int $timeout;

    public function __construct(int $timeout)
    {
        $this->timeout = $timeout;
    }

    public function evaluate(array $context) : bool
    {
        $lastActivity = $context['last_activity'] ?? 0;

        return (time() - $lastActivity) <= $this->timeout;
    }
}