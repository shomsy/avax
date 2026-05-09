<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Flows\GenerateSchemaFromDataObject;

use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;

/**
 * Generates a JSON Schema document from a DataObject class or instance.
 */
final readonly class GenerateSchemaFromDataObject
{
    public function __construct(
        private \Avax\Components\API\SchemaGeneration\System\Capabilities\ReadDataObjectShape\ReadDataObjectShape $readShape,
        private \Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertDataObjectShapeToJsonSchema\ConvertDataObjectShapeToJsonSchema $convert,
    ) {}

    /**
     * @template T of DataObject
     * @param class-string<T>|T $dataObject
     */
    public function execute(string|object $dataObject) : JsonSchemaDocument
    {
        $shape = $this->readShape->read(objectOrClass: $dataObject);

        return $this->convert->convert(shape: $shape);
    }
}
