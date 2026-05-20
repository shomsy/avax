<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Configuration\Builders;

use Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertDataObjectShapeToJsonSchema\ConvertDataObjectShapeToJsonSchema;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ReadDataObjectShape\ReadDataObjectShape;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ResolveRequestSchema\ResolveRequestSchema;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ResolveResponseSchema\ResolveResponseSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateRequestSchema\GenerateRequestSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateResponseSchema\GenerateResponseSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateSchemaFromDataObject\GenerateSchemaFromDataObject;
use Avax\Components\API\SchemaGeneration\System\Flows\ValidatePayloadAgainstSchema\ValidatePayloadAgainstSchema;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;

/**
 * RegisterSchemaGenerationDefaults — mirrors BuildSchemaGeneration using container.
 *
 * The existing BuildSchemaGeneration creates objects with `new` directly.
 * This builder registers the same dependency graph into the container
 * so that the ServiceProvider can resolve them via DI.
 */
final readonly class RegisterSchemaGenerationDefaults
{
    public function register(ContainerInterface $container) : void
    {
        // === Data Shape Inspection ===

        $container->singleton(
            InspectDataShape::class,
            static fn () : InspectDataShape => new InspectDataShape(),
        );

        // === Shape Reading ===

        $container->singleton(
            ReadDataObjectShape::class,
            static fn (ContainerInterface $c) : ReadDataObjectShape => new ReadDataObjectShape(
                inspector: $c->get(InspectDataShape::class),
            ),
        );

        // === Schema Conversion ===

        $container->singleton(
            ConvertDataObjectShapeToJsonSchema::class,
            static fn () : ConvertDataObjectShapeToJsonSchema => new ConvertDataObjectShapeToJsonSchema(),
        );

        // === Schema Resolution ===

        $container->singleton(
            ResolveRequestSchema::class,
            static fn (ContainerInterface $c) : ResolveRequestSchema => new ResolveRequestSchema(
                readShape: $c->get(ReadDataObjectShape::class),
                convert  : $c->get(ConvertDataObjectShapeToJsonSchema::class),
            ),
        );

        $container->singleton(
            ResolveResponseSchema::class,
            static fn (ContainerInterface $c) : ResolveResponseSchema => new ResolveResponseSchema(
                readShape: $c->get(ReadDataObjectShape::class),
                convert  : $c->get(ConvertDataObjectShapeToJsonSchema::class),
            ),
        );

        // === Flows ===

        $container->singleton(
            GenerateSchemaFromDataObject::class,
            static fn (ContainerInterface $c) : GenerateSchemaFromDataObject => new GenerateSchemaFromDataObject(
                readShape: $c->get(ReadDataObjectShape::class),
                convert  : $c->get(ConvertDataObjectShapeToJsonSchema::class),
            ),
        );

        $container->singleton(
            GenerateRequestSchema::class,
            static fn (ContainerInterface $c) : GenerateRequestSchema => new GenerateRequestSchema(
                resolver: $c->get(ResolveRequestSchema::class),
            ),
        );

        $container->singleton(
            GenerateResponseSchema::class,
            static fn (ContainerInterface $c) : GenerateResponseSchema => new GenerateResponseSchema(
                resolver: $c->get(ResolveResponseSchema::class),
            ),
        );

        $container->singleton(
            ValidatePayloadAgainstSchema::class,
            static fn () : ValidatePayloadAgainstSchema => new ValidatePayloadAgainstSchema(),
        );
    }
}
