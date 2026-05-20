<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * GraphQLServiceProvider — registers API/GraphQL component dependencies.
 *
 * Delegates to RegisterGraphQLDefaults builder for dependency wiring.
 */
final class GraphQLServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        (new Builders\RegisterGraphQLDefaults())->register($container);
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
