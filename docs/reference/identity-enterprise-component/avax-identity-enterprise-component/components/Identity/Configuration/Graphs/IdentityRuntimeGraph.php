<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Configuration\Graphs;

use Avax\Components\Identity\Flows\ResetIdentityRuntime\ResetIdentityRuntime;

final readonly class IdentityRuntimeGraph
{
    public function __construct(
        private AuthenticationGraph $authentication,
        private AuthorizationGraph $authorization,
        private SessionGraph $sessions,
        private TokenGraph $tokens,
        private ExternalIdentityGraph $externalIdentity,
        private TenancyGraph $tenancy,
        private ResetIdentityRuntime $resetRuntime,
    ) {}

    public function authentication(): AuthenticationGraph
    {
        return $this->authentication;
    }

    public function authorization(): AuthorizationGraph
    {
        return $this->authorization;
    }

    public function sessions(): SessionGraph
    {
        return $this->sessions;
    }

    public function tokens(): TokenGraph
    {
        return $this->tokens;
    }

    public function externalIdentity(): ExternalIdentityGraph
    {
        return $this->externalIdentity;
    }

    public function tenancy(): TenancyGraph
    {
        return $this->tenancy;
    }

    public function resetRuntime(): ResetIdentityRuntime
    {
        return $this->resetRuntime;
    }
}
