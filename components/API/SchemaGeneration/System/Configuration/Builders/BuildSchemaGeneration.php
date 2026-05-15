<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Configuration\Builders;

use Avax\Components\API\SchemaGeneration\System\Configuration\SchemaGenerationAssembly;

use Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertDataObjectShapeToJsonSchema\ConvertDataObjectShapeToJsonSchema;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ReadDataObjectShape\ReadDataObjectShape;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ResolveRequestSchema\ResolveRequestSchema;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ResolveResponseSchema\ResolveResponseSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateRequestSchema\GenerateRequestSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateResponseSchema\GenerateResponseSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateSchemaFromDataObject\GenerateSchemaFromDataObject;
use Avax\Components\API\SchemaGeneration\System\Flows\ValidatePayloadAgainstSchema\ValidatePayloadAgainstSchema;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;

/**
 * Assembles the SchemaGeneration component.
 */
final readonly class BuildSchemaGeneration
{
    public static function make() : SchemaGenerationAssembly
    {
        $inspectDataShape = new InspectDataShape();
        $readShape = new ReadDataObjectShape(inspector: $inspectDataShape);
        $convertToJsonSchema = new ConvertDataObjectShapeToJsonSchema();
        $resolveRequest = new ResolveRequestSchema(readShape: $readShape, convert: $convertToJsonSchema);
        $resolveResponse = new ResolveResponseSchema(readShape: $readShape, convert: $convertToJsonSchema);

        return new SchemaGenerationAssembly(
            generateSchema: new GenerateSchemaFromDataObject(readShape: $readShape, convert: $convertToJsonSchema),
            generateRequest: new GenerateRequestSchema(resolver: $resolveRequest),
            generateResponse: new GenerateResponseSchema(resolver: $resolveResponse),
            validatePayload: new ValidatePayloadAgainstSchema(),
        );
    }
}
