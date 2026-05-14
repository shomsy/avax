<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\InMemoryIdempotencyStore;
use Override;

/**
 * ResilienceServiceProvider — registers resilience component dependencies.
 */
final readonly class ResilienceServiceProvider implements ServiceProvider
{
    #[Override]
    public function register(ContainerInterface $container): void
    {
        $container->singleton(
            abstract: InMemoryIdempotencyStore::class,
            concrete: static fn (): InMemoryIdempotencyStore => new InMemoryIdempotencyStore(),
        );
    }

    #[Override]
    public function boot(ContainerInterface $container): void
    {
        // No boot-time actions required.
    }
}
