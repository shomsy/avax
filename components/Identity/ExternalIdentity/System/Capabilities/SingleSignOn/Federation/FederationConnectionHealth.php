<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport;

enum FederationConnectionHealth: string
{
    case UNKNOWN     = 'unknown';
    case HEALTHY     = 'healthy';
    case DEGRADED    = 'degraded';
    case UNAVAILABLE = 'unavailable';
}
