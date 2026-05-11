<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

interface FederationRuntimeInterface
{
    public function startLogin(
        FederationConnection $federationConnection,
        string $redirectUri, string|null $state = null,
    ) : StartedFederatedLogin;

    /**
     * @param array<string, mixed> $payload
     */
    public function completeLogin(
        FederationConnection $federationConnection,
        array                $payload,
    ) : FederatedIdentity;
}
