<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Configuration\Providers;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Security\System\Capabilities\Configuration\SecurityConfigurationStore;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\ApplySecurityChange;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\ApproveSecurityChange;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\BeginSecurityChange;
use Avax\Components\Identity\Security\System\PublicSurface\Security;
use Avax\Components\Identity\Security\System\PublicSurface\SecurityInterface;

final readonly class RegisterSecurityDefaults
{
    public function register(ContainerInterface $container) : void
    {
        // Security configuration store
        $container->singleton(
            SecurityConfigurationStore::class,
            static fn () : SecurityConfigurationStore => new SecurityConfigurationStore(),
        );

        // Flows
        $container->singleton(
            BeginSecurityChange::class,
            static fn (ContainerInterface $c) : BeginSecurityChange => new BeginSecurityChange(
                securityConfigurationStore: $c->get(SecurityConfigurationStore::class),
            ),
        );

        $container->singleton(
            ApproveSecurityChange::class,
            static fn (ContainerInterface $c) : ApproveSecurityChange => new ApproveSecurityChange(
                securityConfigurationStore: $c->get(SecurityConfigurationStore::class),
            ),
        );

        $container->singleton(
            ApplySecurityChange::class,
            static fn (ContainerInterface $c) : ApplySecurityChange => new ApplySecurityChange(
                securityConfigurationStore: $c->get(SecurityConfigurationStore::class),
            ),
        );

        // Public surface
        $container->singleton(
            SecurityInterface::class,
            static fn (ContainerInterface $c) : Security => new Security(
                securityConfigurationStore: $c->get(SecurityConfigurationStore::class),
                beginSecurityChange       : $c->get(BeginSecurityChange::class),
                approveSecurityChange     : $c->get(ApproveSecurityChange::class),
                applySecurityChange       : $c->get(ApplySecurityChange::class),
            ),
        );
    }
}
