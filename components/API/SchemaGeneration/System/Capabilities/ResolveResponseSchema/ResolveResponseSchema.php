<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Capabilities\ResolveResponseSchema;

use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;

/**
 * Resolves a JSON Schema for an outgoing response from a DataObject class.
 * Excludes hidden fields automatically.
 */
final readonly class ResolveResponseSchema
{
    public function __construct(
        private \Avax\Components\API\SchemaGeneration\System\Capabilities\ReadDataObjectShape\ReadDataObjectShape $readShape,
        private \Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertDataObjectShapeToJsonSchema\ConvertDataObjectShapeToJsonSchema $convert,
    ) {}

    /**
     * @template T of DataObject
     * @param class-string<T> $dataObjectClass
     */
    public function resolve(string $dataObjectClass) : JsonSchemaDocument
    {
        $shape = $this->readShape->read(objectOrClass: $dataObjectClass);

        return $this->convert->convert(shape: $shape);
    }
}
