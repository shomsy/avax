<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Scim;

/**
 * Operational health of a SCIM directory.
 */
enum ScimDirectoryHealth: string
{
    case HEALTHY     = 'healthy';
    case DEGRADED    = 'degraded';
    case UNAVAILABLE = 'unavailable';
}
