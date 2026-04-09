<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AuthenticateRequest;

/**
 * Request-local storage for the active auth context.
 */
final class CurrentAuthentication
{
    private AuthenticationContext $context;

    public function __construct()
    {
        $this->context = AuthenticationContext::guest();
    }

    public function store(AuthenticationContext $context) : void
    {
        $this->context = $context;
    }

    public function read() : AuthenticationContext
    {
        return $this->context;
    }

    public function clear() : void
    {
        $this->context = AuthenticationContext::guest();
    }
}
