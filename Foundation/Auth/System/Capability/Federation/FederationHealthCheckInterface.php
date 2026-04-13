<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

interface FederationHealthCheckInterface
{
    public function checkHealth(FederationConnection $connection) : FederationConnectionHealth;
}
