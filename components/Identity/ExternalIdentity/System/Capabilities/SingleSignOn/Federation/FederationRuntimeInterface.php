<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

interface FederationRuntimeInterface
{
    public function startLogin(
        FederationConnection $connection,
        string $redirectUri,
        string $state = null,
    ): StartedFederatedLogin;

    /**
     * @param array<string, mixed> $payload
     */
    public function completeLogin(
        FederationConnection $connection,
        array $payload,
    ): FederatedIdentity;
}
