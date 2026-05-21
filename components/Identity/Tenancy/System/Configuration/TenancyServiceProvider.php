<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Context\DefaultTenantContext;
use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContextInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\InMemoryTenantStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\Configuration\Assembly\TenancyGraph;
use Avax\Components\Identity\Tenancy\System\Configuration\TenancyConfiguration;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;

/**
 * TenancyServiceProvider — registers tenancy component dependencies.
 */
final class TenancyServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Tenancy configuration
        $container->singleton(TenancyConfiguration::class, static fn () : TenancyConfiguration => new TenancyConfiguration());

        // Tenant store — in-memory implementation
        $container->singleton(TenantStoreInterface::class, static fn () : TenantStoreInterface => new InMemoryTenantStore());

        $container->scoped(TenantContextInterface::class, static fn () : TenantContextInterface => new DefaultTenantContext());

        $container->scoped(Tenancy::class, static fn (ContainerInterface $c) : Tenancy => TenancyGraph::fromContext(
            context: $c->get(TenantContextInterface::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // Tenant context is scoped; no static worker state reset is needed here.
    }
}
