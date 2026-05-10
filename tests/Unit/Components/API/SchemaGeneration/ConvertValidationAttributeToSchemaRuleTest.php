<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\SchemaGeneration;

use Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertValidationAttributeToSchemaRule\ConvertValidationAttributeToSchemaRule;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Between;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Max;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\RegexPattern;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataFieldType;
use PHPUnit\Framework\TestCase;

final class ConvertValidationAttributeToSchemaRuleTest extends TestCase
{
    public function test_email_attribute_adds_format_to_schema() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(attributes: [new Email()]);

        $result = $converter->apply(field: $field, schema: ['type' => 'string']);

        $this->assertSame('email', $result['format']);
    }

    /**
     * @param list<object> $attributes
     */
    private function makeField(string $type = 'string', array $attributes = []) : DataField
    {
        return new DataField(
            name              : 'testField',
            inputName         : 'testField',
            dataFieldType     : new DataFieldType(names: [$type], allowsNull: true),
            attributes        : $attributes,
            isConstructorField: false,
            isPromotedProperty: false,
            isPublicProperty  : true,
            hasDefaultValue   : false,
            defaultValue      : null,
        );
    }

    public function test_regex_pattern_attribute_adds_pattern_to_schema() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $pattern   = '/^[a-z]+$/';
        $field     = $this->makeField(attributes: [new RegexPattern(pattern: $pattern)]);

        $result = $converter->apply(field: $field, schema: ['type' => 'string']);

        $this->assertSame($pattern, $result['pattern']);
    }

    public function test_min_on_string_maps_to_min_length() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'string',
            attributes: [new Min(min: 5)],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'string']);

        $this->assertSame(5, $result['minLength']);
    }

    public function test_min_on_integer_maps_to_minimum() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'int',
            attributes: [new Min(min: 0)],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'integer']);

        $this->assertSame(0, $result['minimum']);
    }

    public function test_max_on_string_maps_to_max_length() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'string',
            attributes: [new Max(max: 255)],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'string']);

        $this->assertSame(255, $result['maxLength']);
    }

    public function test_max_on_integer_maps_to_maximum() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'int',
            attributes: [new Max(max: 100)],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'integer']);

        $this->assertSame(100, $result['maximum']);
    }

    public function test_between_on_string_maps_to_min_length_and_max_length() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'string',
            attributes: [new Between(min: 3, max: 50)],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'string']);

        $this->assertSame(3, $result['minLength']);
        $this->assertSame(50, $result['maxLength']);
    }

    public function test_between_on_integer_maps_to_minimum_and_maximum() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'int',
            attributes: [new Between(min: 1, max: 10)],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'integer']);

        $this->assertSame(1, $result['minimum']);
        $this->assertSame(10, $result['maximum']);
    }

    public function test_list_of_attribute_maps_to_array_type_with_items() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'array',
            attributes: [new ListOf(class: 'Avax\Tests\Unit\Components\API\SchemaGeneration\ItemDto')],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'string']);

        $this->assertSame('array', $result['type']);
        $this->assertArrayHasKey('items', $result);
        $this->assertSame('object', $result['items']['type']);
    }

    public function test_string_type_attribute_causes_min_to_use_min_length() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'int',
            // No StringType attribute, so int type should use minimum
            attributes: [new Min(min: 5)],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'integer']);

        $this->assertArrayHasKey('minimum', $result);
        $this->assertArrayNotHasKey('minLength', $result);
    }

    public function test_string_type_attribute_forces_min_to_use_min_length() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'int',
            // StringType attribute overrides the type-based decision
            attributes: [new StringType(), new Min(min: 5)],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'string']);

        $this->assertArrayHasKey('minLength', $result);
        $this->assertSame(5, $result['minLength']);
    }

    public function test_string_type_attribute_forces_max_to_use_max_length() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'int',
            attributes: [new StringType(), new Max(max: 100)],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'string']);

        $this->assertArrayHasKey('maxLength', $result);
        $this->assertSame(100, $result['maxLength']);
    }

    public function test_multiple_constraints_combined() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'string',
            attributes: [
                            new Email(),
                            new Min(min: 5),
                            new Max(max: 255),
                        ],
        );

        $result = $converter->apply(field: $field, schema: ['type' => 'string']);

        $this->assertSame('email', $result['format']);
        $this->assertSame(5, $result['minLength']);
        $this->assertSame(255, $result['maxLength']);
    }

    public function test_schema_passed_through_unchanged_without_attributes() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(attributes: []);

        $input  = ['type' => 'string', 'description' => 'A field'];
        $result = $converter->apply(field: $field, schema: $input);

        $this->assertSame('string', $result['type']);
        $this->assertSame('A field', $result['description']);
    }

    public function test_preserves_existing_schema_properties() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(attributes: [new Email()]);

        $input  = ['type' => 'string', 'description' => 'User email', 'example' => 'user@test.com'];
        $result = $converter->apply(field: $field, schema: $input);

        $this->assertSame('User email', $result['description']);
        $this->assertSame('user@test.com', $result['example']);
        $this->assertSame('email', $result['format']);
    }

    public function test_regex_pattern_overwrites_existing_pattern() : void
    {
        $converter  = new ConvertValidationAttributeToSchemaRule();
        $newPattern = '/^[a-z]+$/';
        $field      = $this->makeField(attributes: [new RegexPattern(pattern: $newPattern)]);

        $input  = ['type' => 'string', 'pattern' => '/old-pattern/'];
        $result = $converter->apply(field: $field, schema: $input);

        $this->assertSame($newPattern, $result['pattern']);
    }

    public function test_min_overwrites_existing_minimum() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'int',
            attributes: [new Min(min: 10)],
        );

        $input  = ['type' => 'integer', 'minimum' => 5];
        $result = $converter->apply(field: $field, schema: $input);

        $this->assertSame(10, $result['minimum']);
    }

    public function test_between_overwrites_both_min_and_max() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(
            type      : 'int',
            attributes: [new Between(min: 5, max: 15)],
        );

        $input  = ['type' => 'integer', 'minimum' => 0, 'maximum' => 100];
        $result = $converter->apply(field: $field, schema: $input);

        $this->assertSame(5, $result['minimum']);
        $this->assertSame(15, $result['maximum']);
    }

    public function test_email_format_set_even_if_format_already_exists() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(attributes: [new Email()]);

        $input  = ['type' => 'string', 'format' => 'uri'];
        $result = $converter->apply(field: $field, schema: $input);

        $this->assertSame('email', $result['format']);
    }

    public function test_returns_schema_array_not_modified_by_reference() : void
    {
        $converter = new ConvertValidationAttributeToSchemaRule();
        $field     = $this->makeField(attributes: [new Email()]);

        $input  = ['type' => 'string'];
        $result = $converter->apply(field: $field, schema: $input);

        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('format', $input);
        $this->assertArrayHasKey('format', $result);
    }
}

final class ItemDto
{
    public string $value;
}
