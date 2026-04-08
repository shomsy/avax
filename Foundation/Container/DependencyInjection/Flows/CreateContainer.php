<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\Configuration\Assembly\AssembleObservability;
use Avax\Container\Configuration\Assembly\AssembleRuntime;
use Avax\Container\Configuration\Assembly\SeedSystemServices;
use Avax\Container\Container;
use Avax\Container\Configuration\CreateContainerConfig;

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
        string $cacheDir = '',
        bool $debug = false,
        array $settings = [],
        CreateContainerConfig|null $config = null
        ) : Container {
        $config ??= new CreateContainerConfig(
            cacheDir: $cacheDir,
            debug   : $debug,
            settings: $settings
        );

        $observability = (new AssembleObservability)->assemble(config: $config);
        $runtime = (new AssembleRuntime)->assemble(
            config       : $config,
            observability: $observability
        );
        $telemetry = $runtime->resolver->telemetry();

        $container = new Container(resolver: $runtime->resolver);
        $runtime->resolver->setContainer(container: $container);

        (new SeedSystemServices)->seed(
            runtime       : $runtime,
            observability : $observability,
            container     : $container,
            config        : $config,
            telemetry     : $telemetry
        );

        return $container;
    }
}
