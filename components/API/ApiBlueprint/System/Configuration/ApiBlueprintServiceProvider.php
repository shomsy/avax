<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Configuration;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityChangeDetector;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Documentation\ApiDocumentationSource;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Documentation\InMemoryApiDocumentationSource;
use Avax\Components\API\ApiBlueprint\System\Flows\AnalyzeApiEvolution\AnalyzeApiEvolution;
use Avax\Components\API\ApiBlueprint\System\Flows\DefineApiBlueprint\DefineApiBlueprint;
use Avax\Components\API\ApiBlueprint\System\Flows\VerifyApiBlueprint\VerifyApiBlueprint;
use Avax\Components\API\ApiBlueprint\System\Flows\VerifyApiCompatibility\VerifyApiCompatibility;
use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * ApiBlueprintServiceProvider — registers API/ApiBlueprint component dependencies.
 *
 * All dependencies are registered inline because the component has no complex
 * dependency graph requiring a separate builder.
 */
final class ApiBlueprintServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // === Documentation ===

        $container->singleton(
            ApiDocumentationSource::class,
            static fn () : InMemoryApiDocumentationSource => new InMemoryApiDocumentationSource(),
        );

        // === Flows ===

        $container->singleton(
            DefineApiBlueprint::class,
            static fn (ContainerInterface $c) : DefineApiBlueprint => new DefineApiBlueprint(
                apiDocumentation: $c->get(ApiDocumentationSource::class),
            ),
        );

        $container->singleton(
            VerifyApiBlueprint::class,
            static fn () : VerifyApiBlueprint => new VerifyApiBlueprint(),
        );

        // === Compatibility ===

        $container->singleton(
            CompatibilityChangeDetector::class,
            static fn () : CompatibilityChangeDetector => new CompatibilityChangeDetector(),
        );

        $container->singleton(
            AnalyzeApiEvolution::class,
            static fn (ContainerInterface $c) : AnalyzeApiEvolution => new AnalyzeApiEvolution(
                compatibilityChangeDetector: $c->get(CompatibilityChangeDetector::class),
            ),
        );

        $container->singleton(
            VerifyApiCompatibility::class,
            static fn () : VerifyApiCompatibility => new VerifyApiCompatibility(),
        );
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
