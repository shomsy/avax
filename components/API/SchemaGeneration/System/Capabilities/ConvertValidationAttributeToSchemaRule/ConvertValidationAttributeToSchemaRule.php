<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertValidationAttributeToSchemaRule;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Between;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Max;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\RegexPattern;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;

/**
 * Maps DataTransfer validation attributes to JSON Schema constraints.
 */
final readonly class ConvertValidationAttributeToSchemaRule
{
    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    public function apply(DataField $field, array $schema) : array
    {
        if ($field->hasAttribute(attributeClass: Email::class)) {
            $schema['format'] = 'email';
        }

        if ($field->hasAttribute(attributeClass: RegexPattern::class)) {
            $pattern = $field->firstAttribute(attributeClass: RegexPattern::class);

            if ($pattern instanceof RegexPattern) {
                $schema['pattern'] = $pattern->pattern;
            }
        }

        if ($field->hasAttribute(attributeClass: Min::class)) {
            $min = $field->firstAttribute(attributeClass: Min::class);

            if ($min instanceof Min) {
                if ($field->hasAttribute(attributeClass: StringType::class) || $field->dataFieldType->primaryName() === 'string') {
                    $schema['minLength'] = $min->min;
                } else {
                    $schema['minimum'] = $min->min;
                }
            }
        }

        if ($field->hasAttribute(attributeClass: Max::class)) {
            $max = $field->firstAttribute(attributeClass: Max::class);

            if ($max instanceof Max) {
                if ($field->hasAttribute(attributeClass: StringType::class) || $field->dataFieldType->primaryName() === 'string') {
                    $schema['maxLength'] = $max->max;
                } else {
                    $schema['maximum'] = $max->max;
                }
            }
        }

        if ($field->hasAttribute(attributeClass: Between::class)) {
            $between = $field->firstAttribute(attributeClass: Between::class);

            if ($between instanceof Between) {
                if ($field->hasAttribute(attributeClass: StringType::class) || $field->dataFieldType->primaryName() === 'string') {
                    $schema['minLength'] = $between->min;
                    $schema['maxLength'] = $between->max;
                } else {
                    $schema['minimum'] = $between->min;
                    $schema['maximum'] = $between->max;
                }
            }
        }

        if ($field->hasAttribute(attributeClass: ListOf::class)) {
            $listOf = $field->firstAttribute(attributeClass: ListOf::class);

            if ($listOf instanceof ListOf) {
                $schema['type'] = 'array';

                if ($listOfClass = $field->listItemClass()) {
                    $schema['items'] = ['type' => 'object', 'title' => $listOfClass];
                }
            }
        }

        return $schema;
    }
}
