<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories;

/**
 * Operational health of a SCIM directory.
 */
enum ScimDirectoryHealth: string
{
    case HEALTHY = 'healthy';
    case DEGRADED = 'degraded';
    case UNAVAILABLE = 'unavailable';
}
