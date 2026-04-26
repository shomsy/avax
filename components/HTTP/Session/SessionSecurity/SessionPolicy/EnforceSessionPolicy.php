<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionSecurity\SessionPolicy;

final class EnforceSessionPolicy
{
    private $policy;

    public function __construct($policy)
    {
        $this->policy = $policy;
    }

    public function handle(array $context) : bool
    {
        return $this->policy->check($context);
    }
}