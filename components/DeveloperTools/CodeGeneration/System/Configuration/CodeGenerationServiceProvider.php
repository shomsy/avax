<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\DeveloperTools\CodeGeneration\System\Capabilities\Generators\ControllerGenerator;
use Avax\Components\DeveloperTools\CodeGeneration\System\PublicSurface\CodeGenerator;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * CodeGenerationServiceProvider — registers DeveloperTools/CodeGeneration component dependencies.
 *
 * Registers the CodeGenerator (simple template variable replacer, no deps) and
 * ControllerGenerator (needs Filesystem for writing generated files).
 */
final class CodeGenerationServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        (new Builders\RegisterCodeGenerationDefaults())->register($container);
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
