<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\Federation;

interface FederationHealthCheckInterface
{
    public function checkHealth(FederationConnection $federationConnection) : FederationConnectionHealth;
}
