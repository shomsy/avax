<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest;

/**
 * ServerRequest-local storage for the active auth context.
 */
final class CurrentAuthentication
{
    private AuthenticationContext $authenticationContext;

    public function __construct()
    {
        $this->authenticationContext = AuthenticationContext::guest();
    }

    public function store(AuthenticationContext $authenticationContext): void
    {
        $this->authenticationContext = $authenticationContext;
    }

    public function read(): AuthenticationContext
    {
        return $this->authenticationContext;
    }

    public function clear(): void
    {
        $this->authenticationContext = AuthenticationContext::guest();
    }
}
