<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Flows\CreateContainer;

use Avax\Components\Application\Container\DI\Capabilities\Composition\Assembly\AssembleObservability;
use Avax\Components\Application\Container\DI\Capabilities\Composition\Assembly\AssembleRuntime;
use Avax\Components\Application\Container\DI\Capabilities\Composition\Assembly\SeedSystemServices;
use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\DI\Container;
use InvalidArgumentException;

/**
 * Assembles one fully wired container instance and its system services.
 */
final class CreateContainer
{
    /**
     * Returns one ready-to-use container instance.
     *
     * @param array<string, mixed> $settings
     */
    public function create(
        string|null                $cacheDir = null,
        bool|null                  $debug = null,
        array|null                 $settings = null,
        CreateContainerConfig|null $config = null
    ) : Container
    {
        $cacheDir ??= '';
        $debug    ??= false;
        $settings ??= [];
        $config   ??= new CreateContainerConfig(
            cacheDir: $cacheDir,
            debug   : $debug,
            settings: $settings
        );

        if (! $config->supportsAsyncTarget()) {
            throw new InvalidArgumentException(
                message: "Async target [{$config->asyncTarget}] is not supported by this container runtime. "
                         . 'Supported targets are [fpm, worker].'
            );
        }

        $observability = (new AssembleObservability)->assemble(config: $config);
        $runtime       = (new AssembleRuntime)->assemble(
            config       : $config,
            observability: $observability
        );
        $telemetry     = $runtime->resolver->telemetry();

        $container = new Container(resolver: $runtime->resolver);
        $runtime->resolver->setContainer(container: $container);

        (new SeedSystemServices)->seed(
            runtime      : $runtime,
            observability: $observability,
            container    : $container,
            config       : $config,
            telemetry    : $telemetry
        );

        return $container;
    }
}
