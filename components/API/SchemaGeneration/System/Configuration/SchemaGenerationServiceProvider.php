<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * SchemaGenerationServiceProvider — registers API/SchemaGeneration component dependencies.
 *
 * Delegates to RegisterSchemaGenerationDefaults builder which mirrors the
 * existing BuildSchemaGeneration assembly using container-based DI.
 */
final class SchemaGenerationServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        (new Builders\RegisterSchemaGenerationDefaults())->register($container);
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
