<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Federation;

interface FederationRuntimeInterface
{
    public function startLogin(
        FederationConnection $connection,
        string               $redirectUri,
        string|null          $state = null
    ) : StartedFederatedLogin;

    /**
     * @param array<string, mixed> $payload
     */
    public function completeLogin(
        FederationConnection $connection,
        array                $payload
    ) : FederatedIdentity;
}
