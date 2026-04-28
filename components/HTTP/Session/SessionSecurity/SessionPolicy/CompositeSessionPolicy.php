<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionSecurity\SessionPolicy;

final class CompositeSessionPolicy
{
    private array $policies = [];

    public function add(SessionPolicy $policy) : void
    {
        $this->policies[] = $policy;
    }

    public function check(array $context) : bool
    {
        foreach ($this->policies as $policy) {
            if (! $policy->evaluate($context)) {
                return false;
            }
        }

        return true;
    }
}