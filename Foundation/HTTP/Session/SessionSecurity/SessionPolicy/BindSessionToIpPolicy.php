<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionPolicy;

final class BindSessionToIpPolicy
{
    private array $allowedIps = [];

    public function __construct(array $allowedIps = [])
    {
        $this->allowedIps = $allowedIps;
    }

    public function evaluate(array $context) : bool
    {
        if (empty($this->allowedIps)) {
            return true;
        }

        $currentIp = $context['ip_address'] ?? '';

        return in_array($currentIp, $this->allowedIps);
    }
}