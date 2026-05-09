<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertDataObjectShapeToJsonSchema;

use Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertValidationAttributeToSchemaRule\ConvertValidationAttributeToSchemaRule;
use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\API\SchemaGeneration\System\Foundation\SchemaGenerationFailed;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShape;

/**
 * Converts a DataShape into a JSON Schema document.
 */
final readonly class ConvertDataObjectShapeToJsonSchema
{
    public function __construct(
        private readonly ConvertValidationAttributeToSchemaRule $ruleConverter = new ConvertValidationAttributeToSchemaRule(),
    ) {}

    public function convert(DataShape $shape) : JsonSchemaDocument
    {
        $properties = [];
        $required = [];

        foreach ($shape->fields() as $name => $field) {
            if ($field->isHidden()) {
                continue;
            }

            $properties[$name] = $this->convertField(field: $field);

            if ($field->isRequired()) {
                $required[] = $name;
            }
        }

        $schema = [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'type' => 'object',
            'properties' => $properties,
        ];

        if ($required !== []) {
            $schema['required'] = $required;
        }

        return new JsonSchemaDocument(
            schemaId: $shape->class,
            schema  : $schema,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function convertField(DataField $field) : array
    {
        $schema = $this->typeToJsonSchemaFragment(type: $field->dataFieldType, context: $field->name);

        $schema = $this->ruleConverter->apply(field: $field, schema: $schema);

        if ($field->hasDefaultValue || $field->hasDefaultAttribute()) {
            $default = $field->hasDefaultAttribute() ? $field->defaultFromAttribute() : $field->defaultValue;

            if ($default !== null) {
                $schema['default'] = $default;
            }
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private function typeToJsonSchemaFragment(\Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataFieldType $type, string $context) : array
    {
        $primary = $type->primaryName();

        if ($type->isBackedEnum()) {
            return ['type' => 'string'];
        }

        $jsonType = match ($primary) {
            'string' => 'string',
            'int' => 'integer',
            'float' => 'number',
            'bool' => 'boolean',
            'array' => 'object',
            'mixed' => null,
            default => null,
        };

        if ($jsonType !== null) {
            return ['type' => $jsonType];
        }

        if ($primary === 'array') {
            return ['type' => 'object'];
        }

        if ($primary !== null && class_exists(class: $primary)) {
            return ['type' => 'object'];
        }

        throw SchemaGenerationFailed::unsupportedType(type: $primary ?? 'unknown', context: $context);
    }
}
