<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionPolicy;

final class BindSessionToUserAgentPolicy
{
    public function evaluate(array $context) : bool
    {
        $expected = $context['user_agent'] ?? '';
        $actual   = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return $expected === $actual;
    }
}