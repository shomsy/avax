<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionPolicy;

final class SessionPolicy
{
    private array $policies = [];

    public function add(callable $policy) : void
    {
        $this->policies[] = $policy;
    }

    public function check(array $context) : bool
    {
        foreach ($this->policies as $policy) {
            if ($policy($context) === false) {
                return false;
            }
        }

        return true;
    }
}