<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Context\DefaultTenantContext;
use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContext;
use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContextInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\InMemoryTenantStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\Configuration\TenancyConfiguration;

/**
 * TenancyServiceProvider — registers tenancy component dependencies.
 */
final class TenancyServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Tenancy configuration
        $container->singleton(TenancyConfiguration::class, static fn () : TenancyConfiguration => new TenancyConfiguration());

        // Tenant context — request-scoped tenant identification
        $container->singleton(TenantContextInterface::class, static fn () : TenantContextInterface => new DefaultTenantContext());

        // Tenant store — in-memory implementation
        $container->singleton(TenantStoreInterface::class, static fn () : TenantStoreInterface => new InMemoryTenantStore());
    }

    public function boot(ContainerInterface $container) : void
    {
        // Clear tenant context for worker safety
        TenantContext::reset();
    }
}
