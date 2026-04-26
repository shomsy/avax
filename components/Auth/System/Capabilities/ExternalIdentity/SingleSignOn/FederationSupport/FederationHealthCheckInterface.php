<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

interface FederationHealthCheckInterface
{
    public function checkHealth(FederationConnection $connection) : FederationConnectionHealth;
}
