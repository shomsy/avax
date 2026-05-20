<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Configuration\Builders;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\DeveloperTools\CodeGeneration\System\Capabilities\Generators\ControllerGenerator;
use Avax\Components\DeveloperTools\CodeGeneration\System\PublicSurface\CodeGenerator;

final readonly class RegisterCodeGenerationDefaults
{
    public function register(ContainerInterface $container) : void
    {
        // === Code Generation Infrastructure ===

        // Filesystem — used by generators to write generated files
        $container->singleton(Filesystem::class, static fn () : Filesystem => new Filesystem());

        // ControllerGenerator — generates controller class stubs (needs Filesystem)
        $container->singleton(
            ControllerGenerator::class,
            static fn (ContainerInterface $c) : ControllerGenerator => new ControllerGenerator(
                filesystem: $c->get(Filesystem::class),
            ),
        );

        // CodeGenerator — simple template variable replacer (no deps)
        $container->singleton(CodeGenerator::class, static fn () : CodeGenerator => new CodeGenerator());
    }
}
