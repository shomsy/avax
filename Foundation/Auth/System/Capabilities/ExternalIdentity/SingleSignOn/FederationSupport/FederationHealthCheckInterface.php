<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Federation;

interface FederationHealthCheckInterface
{
    public function checkHealth(FederationConnection $connection) : FederationConnectionHealth;
}
