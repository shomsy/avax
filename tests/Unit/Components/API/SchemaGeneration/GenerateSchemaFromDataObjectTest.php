<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\SchemaGeneration;

use Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertDataObjectShapeToJsonSchema\ConvertDataObjectShapeToJsonSchema;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ReadDataObjectShape\ReadDataObjectShape;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateSchemaFromDataObject\GenerateSchemaFromDataObject;
use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\API\SchemaGeneration\System\Foundation\SchemaGenerationFailed;
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
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use NonExistentResourceType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class GenerateSchemaFromDataObjectTest extends TestCase
{
    public function test_execute_from_class_string_produces_schema() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: BasicUserDto::class);

        $this->assertInstanceOf(JsonSchemaDocument::class, $schema);
        $this->assertSame(BasicUserDto::class, $schema->schemaId);
    }

    public function test_execute_from_object_instance_produces_schema() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $instance        = new UserInstanceDto();
        $instance->title = 'Test';
        $instance->count = 10;

        $schema = $flow->execute(dataObject: $instance);

        $this->assertInstanceOf(JsonSchemaDocument::class, $schema);
        $this->assertSame(UserInstanceDto::class, $schema->schemaId);
    }

    public function test_schema_contains_json_schema_draft_identifier() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: BasicUserDto::class);

        $this->assertArrayHasKey('$schema', $schema->schema);
        $this->assertSame('https://json-schema.org/draft/2020-12/schema', $schema->schema['$schema']);
    }

    public function test_schema_type_is_object() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: BasicUserDto::class);

        $this->assertSame('object', $schema->schema['type']);
    }

    public function test_required_fields_are_included_in_required_array() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: BasicUserDto::class);

        $this->assertArrayHasKey('required', $schema->schema);
        $this->assertContains('email', $schema->schema['required']);
        $this->assertNotContains('bio', $schema->schema['required']);
    }

    public function test_hidden_fields_are_excluded_from_properties() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: SecretUserDto::class);

        $this->assertArrayHasKey('properties', $schema->schema);
        $this->assertArrayHasKey('username', $schema->schema['properties']);
        $this->assertArrayNotHasKey('apiKey', $schema->schema['properties']);
        $this->assertArrayNotHasKey('internalId', $schema->schema['properties']);
    }

    public function test_string_fields_produce_string_type() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: BasicUserDto::class);

        $this->assertSame('string', $schema->schema['properties']['email']['type']);
        $this->assertSame('string', $schema->schema['properties']['bio']['type']);
    }

    public function test_integer_fields_produce_integer_type() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: NumericUserDto::class);

        $this->assertSame('integer', $schema->schema['properties']['age']['type']);
        $this->assertSame('integer', $schema->schema['properties']['score']['type']);
    }

    public function test_backed_enum_fields_produce_string_type() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: EnumUserDto::class);

        $this->assertSame('string', $schema->schema['properties']['status']['type']);
    }

    public function test_email_attribute_adds_format_constraint() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: BasicUserDto::class);

        $this->assertSame('email', $schema->schema['properties']['email']['format']);
    }

    public function test_regex_pattern_attribute_adds_pattern_constraint() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: PatternUserDto::class);

        $this->assertArrayHasKey('pattern', $schema->schema['properties']['slug']);
        $this->assertSame('/^[a-z0-9-]+$/', $schema->schema['properties']['slug']['pattern']);
    }

    public function test_min_max_on_strings_map_to_min_length_max_length() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: ConstrainedStringDto::class);

        $this->assertSame(3, $schema->schema['properties']['username']['minLength']);
        $this->assertSame(50, $schema->schema['properties']['username']['maxLength']);
    }

    public function test_min_max_on_integers_map_to_minimum_maximum() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: ConstrainedIntDto::class);

        $this->assertSame(0, $schema->schema['properties']['count']['minimum']);
        $this->assertSame(100, $schema->schema['properties']['count']['maximum']);
    }

    public function test_list_of_attribute_produces_array_type_with_items() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: ListUserDto::class);

        $this->assertSame('array', $schema->schema['properties']['tags']['type']);
        $this->assertArrayHasKey('items', $schema->schema['properties']['tags']);
        $this->assertSame('object', $schema->schema['properties']['tags']['items']['type']);
    }

    public function test_no_required_fields_omits_required_key() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: AllOptionalDto::class);

        $this->assertArrayNotHasKey('required', $schema->schema);
    }

    public function test_empty_data_object_produces_valid_schema() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: EmptyDto::class);

        $this->assertSame('object', $schema->schema['type']);
        $this->assertSame([], $schema->schema['properties']);
        $this->assertArrayNotHasKey('required', $schema->schema);
    }

    public function test_class_type_maps_to_object_type() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: UnsupportedTypeDto::class);

        // stdClass is a class, so it maps to 'object' type
        $this->assertSame('object', $schema->schema['properties']['weirdField']['type']);
    }

    public function test_throws_on_truly_unsupported_type() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $this->expectException(SchemaGenerationFailed::class);
        $this->expectExceptionMessage("Cannot convert type 'NonExistentResourceType' to JSON Schema");

        $flow->execute(dataObject: TrulyUnsupportedTypeDto::class);
    }

    public function test_multiple_hidden_fields_all_excluded() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: MultipleHiddenDto::class);

        $properties = $schema->schema['properties'];
        $this->assertArrayHasKey('publicName', $properties);
        $this->assertArrayNotHasKey('secretA', $properties);
        $this->assertArrayNotHasKey('secretB', $properties);
        $this->assertArrayNotHasKey('secretC', $properties);
    }

    public function test_schema_document_to_json_is_valid_json() : void
    {
        $flow = new GenerateSchemaFromDataObject(
            readShape: new ReadDataObjectShape(),
            convert  : new ConvertDataObjectShapeToJsonSchema(),
        );

        $schema = $flow->execute(dataObject: BasicUserDto::class);
        $json   = $schema->toJson();

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('type', $decoded);
        $this->assertArrayHasKey('properties', $decoded);
    }
}

// -- Test DataObjects --

final class BasicUserDto extends DataObject
{
    #[Required]
    #[StringType]
    #[Email]
    public string $email;

    #[Optional]
    #[StringType]
    public string|null $bio = null;
}

final class UserInstanceDto
{
    #[Required]
    public string $title;

    #[Required]
    public int $count;
}

final class SecretUserDto extends DataObject
{
    #[Required]
    #[StringType]
    public string $username;

    #[Hidden]
    public string $apiKey;

    #[Hidden]
    public string $internalId;
}

final class NumericUserDto extends DataObject
{
    #[Required]
    #[IntegerType]
    public int $age;

    #[Optional]
    #[IntegerType]
    public int|null $score = null;
}

enum UserStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Banned   = 'banned';
}

final class EnumUserDto extends DataObject
{
    #[Required]
    public UserStatus $status;
}

final class PatternUserDto extends DataObject
{
    #[Required]
    #[StringType]
    #[RegexPattern(pattern: '/^[a-z0-9-]+$/')]
    public string $slug;
}

final class ConstrainedStringDto extends DataObject
{
    #[Required]
    #[StringType]
    #[Min(min: 3)]
    #[Max(max: 50)]
    public string $username;
}

final class ConstrainedIntDto extends DataObject
{
    #[Required]
    #[IntegerType]
    #[Min(min: 0)]
    #[Max(max: 100)]
    public int $count;
}

final class ListUserDto extends DataObject
{
    #[Required]
    #[ListOf(class: BasicUserDto::class)]
    public array $tags;
}

final class AllOptionalDto extends DataObject
{
    #[Optional]
    #[StringType]
    public string|null $a = null;

    #[Optional]
    #[IntegerType]
    public int|null $b = null;
}

final class EmptyDto extends DataObject {}

final class UnsupportedTypeDto
{
    #[Required]
    public stdClass $weirdField;
}

final class MultipleHiddenDto extends DataObject
{
    #[Required]
    #[StringType]
    public string $publicName;

    #[Hidden]
    public string $secretA;

    #[Hidden]
    public string $secretB;

    #[Hidden]
    public string $secretC;
}

final class TrulyUnsupportedTypeDto
{
    #[Required]
    public NonExistentResourceType $weirdField;
}
