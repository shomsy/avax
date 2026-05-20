<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Configuration;

use Avax\Components\API\OpenAPI\System\Capabilities\ErrorResponseSchemaGeneration\BuildErrorResponseSchema;
use Avax\Components\API\OpenAPI\System\Capabilities\PayloadSchemaGeneration\BuildPayloadSchema;
use Avax\Components\API\OpenAPI\System\Capabilities\RenderOpenApiJson\RenderOpenApiJson;
use Avax\Components\API\OpenAPI\System\Capabilities\RenderOpenApiYaml\RenderOpenApiYaml;
use Avax\Components\API\OpenAPI\System\Capabilities\SchemaGeneration\BuildOpenApiDocument;
use Avax\Components\API\OpenAPI\System\Flows\CompareOpenApiDocuments\CompareOpenApiDocuments;
use Avax\Components\API\OpenAPI\System\Flows\ExportOpenApiDocument\ExportOpenApiDocument;
use Avax\Components\API\OpenAPI\System\Flows\ValidateOpenApiDocument\ValidateOpenApiDocument;
use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * OpenAPIServiceProvider — registers API/OpenAPI component dependencies.
 *
 * All dependencies are registered inline because the component has no complex
 * dependency graph requiring a separate builder.
 */
final class OpenAPIServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // === Schema Generation Capabilities ===

        $container->singleton(
            BuildPayloadSchema::class,
            static fn () : BuildPayloadSchema => new BuildPayloadSchema(),
        );

        $container->singleton(
            BuildErrorResponseSchema::class,
            static fn () : BuildErrorResponseSchema => new BuildErrorResponseSchema(),
        );

        $container->singleton(
            OpenApiConfiguration::class,
            static fn () : OpenApiConfiguration => new OpenApiConfiguration(),
        );

        $container->singleton(
            BuildOpenApiDocument::class,
            static fn (ContainerInterface $c) : BuildOpenApiDocument => new BuildOpenApiDocument(
                configuration        : $c->get(OpenApiConfiguration::class),
                buildPayloadSchema   : $c->get(BuildPayloadSchema::class),
                buildErrorResponseSchema: $c->get(BuildErrorResponseSchema::class),
            ),
        );

        // === Flows ===

        $container->singleton(
            ExportOpenApiDocument::class,
            static fn (ContainerInterface $c) : ExportOpenApiDocument => new ExportOpenApiDocument(
                buildOpenApiDocument: $c->get(BuildOpenApiDocument::class),
            ),
        );

        $container->singleton(
            ValidateOpenApiDocument::class,
            static fn () : ValidateOpenApiDocument => new ValidateOpenApiDocument(),
        );

        $container->singleton(
            CompareOpenApiDocuments::class,
            static fn () : CompareOpenApiDocuments => new CompareOpenApiDocuments(),
        );

        // === Rendering ===

        $container->singleton(
            RenderOpenApiJson::class,
            static fn () : RenderOpenApiJson => new RenderOpenApiJson(),
        );

        $container->singleton(
            RenderOpenApiYaml::class,
            static fn () : RenderOpenApiYaml => new RenderOpenApiYaml(),
        );
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
