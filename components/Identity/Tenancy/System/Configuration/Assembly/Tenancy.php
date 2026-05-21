<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Configuration\Assembly;

use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\InMemoryAdminElevationStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContextInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\TenancyRuntime\TenancyRuntime;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Admin;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy as TenancySurface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

/**
 * Assembles the Tenancy public surface from explicit runtime dependencies.
 */
final class Tenancy
{
    public static function fromContext(TenantContextInterface $context) : TenancySurface
    {
        return new TenancySurface(
            runtime: new TenancyRuntime(
                tenantContext: $context,
                admin        : new Admin(
                    store: new InMemoryAdminElevationStore(),
                    clock: new Clock(),
                ),
            ),
        );
    }
}
