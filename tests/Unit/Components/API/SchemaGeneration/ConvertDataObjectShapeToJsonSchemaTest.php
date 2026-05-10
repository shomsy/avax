<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\SchemaGeneration;

use Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertDataObjectShapeToJsonSchema\ConvertDataObjectShapeToJsonSchema;
use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\API\SchemaGeneration\System\Foundation\SchemaGenerationFailed;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Between;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Max;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\RegexPattern;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataFieldType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShape;
use PHPUnit\Framework\TestCase;

final class ConvertDataObjectShapeToJsonSchemaTest extends TestCase
{
    public function test_convert_string_field_to_string_type() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'name' => $this->field(type: 'string', required: true),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertInstanceOf(JsonSchemaDocument::class, $result);
        $this->assertSame('string', $result->schema['properties']['name']['type']);
    }

    /**
     * @param array<string, DataField> $fields
     */
    private function shapeWithFields(array $fields) : DataShape
    {
        return new DataShape(
            class : 'Avax\Tests\Unit\Components\API\SchemaGeneration\TestDto',
            fields: $fields,
        );
    }

    /**
     * @param list<object> $attributes
     */
    private function field(
        string $type = 'string',
        bool   $required = false,
        bool   $hidden = false,
        array  $attributes = [],
    ) : DataField
    {
        $attrs = $attributes;

        if ($required) {
            $attrs[] = new Required();
        }

        if ($hidden) {
            $attrs[] = new Hidden();
        }

        return new DataField(
            name              : 'field',
            inputName         : 'field',
            dataFieldType     : new DataFieldType(names: [$type], allowsNull: ! $required),
            attributes        : $attrs,
            isConstructorField: false,
            isPromotedProperty: false,
            isPublicProperty  : true,
            hasDefaultValue   : false,
            defaultValue      : null,
        );
    }

    public function test_convert_int_field_to_integer_type() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'age' => $this->field(type: 'int', required: true),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame('integer', $result->schema['properties']['age']['type']);
    }

    public function test_convert_float_field_to_number_type() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'price' => $this->field(type: 'float', required: true),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame('number', $result->schema['properties']['price']['type']);
    }

    public function test_convert_bool_field_to_boolean_type() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'active' => $this->field(type: 'bool', required: true),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame('boolean', $result->schema['properties']['active']['type']);
    }

    public function test_convert_array_field_to_object_type() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'metadata' => $this->field(type: 'array', required: true),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame('object', $result->schema['properties']['metadata']['type']);
    }

    public function test_convert_backed_enum_to_string_type() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        // Use a DataObject that has a backed enum property so the inspector picks it up
        $shape = $this->shapeWithFields([
                                            'status' => $this->field(type: Status::class, required: true),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame('string', $result->schema['properties']['status']['type']);
    }

    public function test_hidden_fields_are_excluded() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'username' => $this->field(type: 'string', required: true),
                                            'secret'   => $this->field(type: 'string', required: true, hidden: true),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertArrayHasKey('username', $result->schema['properties']);
        $this->assertArrayNotHasKey('secret', $result->schema['properties']);
    }

    public function test_required_fields_appear_in_required_array() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'name'  => $this->field(type: 'string', required: true),
                                            'email' => $this->field(type: 'string', required: true),
                                            'bio'   => $this->field(type: 'string', required: false),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertContains('name', $result->schema['required']);
        $this->assertContains('email', $result->schema['required']);
        $this->assertNotContains('bio', $result->schema['required']);
    }

    public function test_no_required_fields_omits_required_key() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'name' => $this->field(type: 'string', required: false),
                                            'bio'  => $this->field(type: 'string', required: false),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertArrayNotHasKey('required', $result->schema);
    }

    public function test_email_attribute_adds_format_constraint() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'email' => $this->field(type: 'string', required: true, attributes: [new Email()]),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame('email', $result->schema['properties']['email']['format']);
    }

    public function test_regex_pattern_adds_pattern_constraint() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $pattern = '/^[a-z]+$/';
        $shape   = $this->shapeWithFields([
                                              'slug' => $this->field(type: 'string', required: true, attributes: [new RegexPattern(pattern: $pattern)]),
                                          ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame($pattern, $result->schema['properties']['slug']['pattern']);
    }

    public function test_min_on_string_maps_to_min_length() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'name' => $this->field(
                                                type      : 'string',
                                                required  : true,
                                                attributes: [new Min(min: 3)],
                                            ),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame(3, $result->schema['properties']['name']['minLength']);
    }

    public function test_min_on_integer_maps_to_minimum() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'age' => $this->field(
                                                type      : 'int',
                                                required  : true,
                                                attributes: [new Min(min: 0)],
                                            ),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame(0, $result->schema['properties']['age']['minimum']);
    }

    public function test_max_on_string_maps_to_max_length() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'name' => $this->field(
                                                type      : 'string',
                                                required  : true,
                                                attributes: [new Max(max: 100)],
                                            ),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame(100, $result->schema['properties']['name']['maxLength']);
    }

    public function test_max_on_integer_maps_to_maximum() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'age' => $this->field(
                                                type      : 'int',
                                                required  : true,
                                                attributes: [new Max(max: 150)],
                                            ),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame(150, $result->schema['properties']['age']['maximum']);
    }

    public function test_between_on_string_maps_to_min_length_and_max_length() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'name' => $this->field(
                                                type      : 'string',
                                                required  : true,
                                                attributes: [new Between(min: 3, max: 50)],
                                            ),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame(3, $result->schema['properties']['name']['minLength']);
        $this->assertSame(50, $result->schema['properties']['name']['maxLength']);
    }

    public function test_between_on_integer_maps_to_minimum_and_maximum() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'score' => $this->field(
                                                type      : 'int',
                                                required  : true,
                                                attributes: [new Between(min: 0, max: 100)],
                                            ),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame(0, $result->schema['properties']['score']['minimum']);
        $this->assertSame(100, $result->schema['properties']['score']['maximum']);
    }

    public function test_list_of_maps_to_array_type_with_items() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $listOfAttr = new ListOf(class: 'Avax\Tests\Unit\Components\API\SchemaGeneration\ListItemDto');
        $shape      = $this->shapeWithFields([
                                                 'items' => $this->field(
                                                     type      : 'array',
                                                     required  : true,
                                                     attributes: [$listOfAttr],
                                                 ),
                                             ]);

        $result = $converter->convert(shape: $shape);

        $this->assertSame('array', $result->schema['properties']['items']['type']);
        $this->assertArrayHasKey('items', $result->schema['properties']['items']);
        $this->assertSame('object', $result->schema['properties']['items']['items']['type']);
    }

    public function test_throws_on_unsupported_type() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        // Use a type name that is not a scalar, not 'array', not 'mixed', and not a real class
        $shape = $this->shapeWithFields([
                                            'resource' => $this->field(type: 'SplFileInfoFakeNonExistent', required: true),
                                        ]);

        $this->expectException(SchemaGenerationFailed::class);
        $this->expectExceptionMessage("Cannot convert type 'SplFileInfoFakeNonExistent' to JSON Schema");

        $converter->convert(shape: $shape);
    }

    public function test_schema_id_matches_class_name() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = new DataShape(
            class : 'Avax\Tests\Unit\Components\API\SchemaGeneration\TestClassDto',
            fields: [],
        );

        $result = $converter->convert(shape: $shape);

        $this->assertSame('Avax\Tests\Unit\Components\API\SchemaGeneration\TestClassDto', $result->schemaId);
    }

    public function test_empty_shape_produces_valid_schema() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = new DataShape(
            class : 'Avax\Tests\Unit\Components\API\SchemaGeneration\EmptyDto',
            fields: [],
        );

        $result = $converter->convert(shape: $shape);

        $this->assertSame('object', $result->schema['type']);
        $this->assertSame([], $result->schema['properties']);
        $this->assertArrayNotHasKey('required', $result->schema);
        $this->assertArrayHasKey('$schema', $result->schema);
    }

    public function test_multiple_constraints_combined_on_single_field() : void
    {
        $converter = new ConvertDataObjectShapeToJsonSchema();

        $shape = $this->shapeWithFields([
                                            'email' => $this->field(
                                                type      : 'string',
                                                required  : true,
                                                attributes: [
                                                                new Email(),
                                                                new Min(min: 5),
                                                                new Max(max: 255),
                                                            ],
                                            ),
                                        ]);

        $result = $converter->convert(shape: $shape);

        $emailSchema = $result->schema['properties']['email'];
        $this->assertSame('string', $emailSchema['type']);
        $this->assertSame('email', $emailSchema['format']);
        $this->assertSame(5, $emailSchema['minLength']);
        $this->assertSame(255, $emailSchema['maxLength']);
    }
}

enum Status: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}

final class ListItemDto
{
    public string $value;
}
