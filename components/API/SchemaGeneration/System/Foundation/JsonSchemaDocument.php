<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Foundation;

/**
 * JsonSchemaDocument — immutable JSON Schema document value object.
 */
final readonly class JsonSchemaDocument
{
    /**
     * @param array<string, mixed> $schema
     */
    public function __construct(
        public string $schemaId,
        public array $schema,
    ) {}

    public function toJson(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : string
    {
        $encoded = json_encode(value: $this->schema, flags: $flags);

        if ($encoded === false) {
            throw new SchemaGenerationFailed(
                message: 'Failed to encode JSON Schema document.',
            );
        }

        return $encoded;
    }
}
