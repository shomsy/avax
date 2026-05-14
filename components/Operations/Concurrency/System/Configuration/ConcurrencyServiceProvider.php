<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Override;

/**
 * ConcurrencyServiceProvider — registers concurrency component dependencies.
 */
final readonly class ConcurrencyServiceProvider implements ServiceProvider
{
    #[Override]
    public function register(ContainerInterface $container): void
    {
        $container->singleton(
            abstract: ConcurrencyConfig::class,
            concrete: static fn (): ConcurrencyConfig => new ConcurrencyConfig(),
        );
    }

    #[Override]
    public function boot(ContainerInterface $container): void
    {
        // No boot-time actions required.
    }
}
