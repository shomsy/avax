<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Configuration;

use Avax\Components\API\SchemaGeneration\System\Flows\GenerateRequestSchema\GenerateRequestSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateResponseSchema\GenerateResponseSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateSchemaFromDataObject\GenerateSchemaFromDataObject;
use Avax\Components\API\SchemaGeneration\System\Flows\ValidatePayloadAgainstSchema\ValidatePayloadAgainstSchema;

/**
 * Assembled SchemaGeneration flows.
 */
final readonly class SchemaGenerationAssembly
{
    public function __construct(
        public GenerateSchemaFromDataObject $generateSchema,
        public GenerateRequestSchema $generateRequest,
        public GenerateResponseSchema $generateResponse,
        public ValidatePayloadAgainstSchema $validatePayload,
    ) {}
}
