<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

enum FederationConnectionHealth: string
{
    case UNKNOWN = 'unknown';
    case HEALTHY = 'healthy';
    case DEGRADED = 'degraded';
    case UNAVAILABLE = 'unavailable';
}
