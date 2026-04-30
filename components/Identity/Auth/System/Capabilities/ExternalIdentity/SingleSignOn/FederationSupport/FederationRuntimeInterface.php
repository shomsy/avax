<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

interface FederationRuntimeInterface
{
    public function startLogin(
        FederationConnection $connection,
        string $redirectUri,
        string $state = null,
    ) : StartedFederatedLogin;

    /**
     * @param array<string, mixed> $payload
     */
    public function completeLogin(
        FederationConnection $connection,
        array $payload,
    ) : FederatedIdentity;
}
