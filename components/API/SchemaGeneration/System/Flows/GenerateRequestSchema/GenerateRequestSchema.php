<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Flows\GenerateRequestSchema;

use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;

/**
 * Generates a JSON Schema for an incoming request payload from a DataObject.
 */
final readonly class GenerateRequestSchema
{
    public function __construct(
        private \Avax\Components\API\SchemaGeneration\System\Capabilities\ResolveRequestSchema\ResolveRequestSchema $resolver,
    ) {}

    /**
     * @template T of DataObject
     * @param class-string<T> $requestClass
     */
    public function execute(string $requestClass) : JsonSchemaDocument
    {
        return $this->resolver->resolve(dataObjectClass: $requestClass);
    }
}
