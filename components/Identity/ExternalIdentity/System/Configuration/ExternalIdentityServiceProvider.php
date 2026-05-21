<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentity;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\OAuth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\OpenIDConnect;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\SingleSignOn;

/**
 * ExternalIdentityServiceProvider — registers external identity component dependencies.
 */
final class ExternalIdentityServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // External identity capability
        $container->singleton(ExternalIdentity::class, static fn () : ExternalIdentity => new ExternalIdentity());

        // OAuth capability
        $container->singleton(OAuth::class, static fn () : OAuth => new OAuth());

        // OpenID Connect capability
        $container->singleton(OpenIDConnect::class, static fn () : OpenIDConnect => new OpenIDConnect());

        // Single Sign-On capability
        $container->singleton(SingleSignOn::class, static fn () : SingleSignOn => new SingleSignOn());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed for the current capability defaults.
    }
}
