<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Override;

/**
 * RequestServiceProvider — registers HTTP request component dependencies.
 */
final readonly class RequestServiceProvider implements ServiceProvider
{
    #[Override]
    public function register(ContainerInterface $container): void
    {
        $container->singleton(
            abstract: RequestConfiguration::class,
            concrete: static fn (): RequestConfiguration => new RequestConfiguration(),
        );
    }

    #[Override]
    public function boot(ContainerInterface $container): void
    {
        // No boot-time actions required.
    }
}
