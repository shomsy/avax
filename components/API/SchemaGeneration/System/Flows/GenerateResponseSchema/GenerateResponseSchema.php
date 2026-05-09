<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Flows\GenerateResponseSchema;

use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;

/**
 * Generates a JSON Schema for an outgoing response payload from a DataObject.
 * Automatically excludes hidden fields.
 */
final readonly class GenerateResponseSchema
{
    public function __construct(
        private \Avax\Components\API\SchemaGeneration\System\Capabilities\ResolveResponseSchema\ResolveResponseSchema $resolver,
    ) {}

    /**
     * @template T of DataObject
     * @param class-string<T> $responseClass
     */
    public function execute(string $responseClass) : JsonSchemaDocument
    {
        return $this->resolver->resolve(dataObjectClass: $responseClass);
    }
}
