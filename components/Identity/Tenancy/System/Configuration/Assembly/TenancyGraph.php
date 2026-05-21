<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Configuration\Assembly;

use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContextInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\TenancyRuntime\TenancyRuntime;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Admin;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;

/**
 * Assembles the Tenancy public surface from explicit runtime dependencies.
 */
final class TenancyGraph
{
    public static function fromContext(TenantContextInterface $context) : Tenancy
    {
        return new Tenancy(
            runtime: new TenancyRuntime(
                tenantContext: $context,
                admin        : new Admin(),
            ),
        );
    }
}
