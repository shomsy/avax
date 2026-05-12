<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\API\V4SchemaGeneration;

use Avax\Components\API\SchemaGeneration\System\Flows\GenerateSchemaFromDataObject\GenerateSchemaFromDataObject;
use Avax\Components\API\SchemaGeneration\System\Flows\ValidatePayloadAgainstSchema\ValidatePayloadAgainstSchema;
use Avax\Components\API\SchemaGeneration\System\PublicSurface\SchemaGeneration;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Between;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Max;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\RegexPattern;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use PHPUnit\Framework\TestCase;

final class SchemaGenerationTest extends TestCase
{
    protected function tearDown() : void
    {
        SchemaGeneration::setAssembly(assembly: null);
    }

    public function test_it_generates_schema_from_data_object_class() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertSame(TestDataObject::class, $schema->schemaId);
        self::assertArrayHasKey('properties', $schema->schema);
        self::assertArrayHasKey('name', $schema->schema['properties']);
        self::assertArrayHasKey('email', $schema->schema['properties']);
    }

    public function test_it_generates_schema_from_data_object_instance() : void
    {
        $instance = new TestDataObject();
        $instance->name = 'test';
        $instance->email = 'test@example.com';

        $schema = SchemaGeneration::fromDataObject($instance);

        self::assertSame(TestDataObject::class, $schema->schemaId);
    }

    public function test_required_fields_map_to_required_in_schema() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertArrayHasKey('required', $schema->schema);
        self::assertContains('name', $schema->schema['required']);
        self::assertContains('email', $schema->schema['required']);
    }

    public function test_string_type_maps_to_string_json_type() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertSame('string', $schema->schema['properties']['name']['type']);
    }

    public function test_integer_type_maps_to_integer_json_type() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertSame('integer', $schema->schema['properties']['age']['type']);
    }

    public function test_email_attribute_maps_to_email_format() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertSame('email', $schema->schema['properties']['email']['format']);
    }

    public function test_min_attribute_maps_to_minimum_or_min_length() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertSame(2, $schema->schema['properties']['name']['minLength']);
        self::assertSame(0, $schema->schema['properties']['age']['minimum']);
    }

    public function test_max_attribute_maps_to_maximum_or_max_length() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertSame(255, $schema->schema['properties']['name']['maxLength']);
        self::assertSame(200, $schema->schema['properties']['age']['maximum']);
    }

    public function test_regex_pattern_maps_to_pattern() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertSame('/^[A-Za-z]+$/', $schema->schema['properties']['code']['pattern']);
    }

    public function test_hidden_fields_are_excluded() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertArrayNotHasKey('secret', $schema->schema['properties']);
    }

    public function test_between_attribute_maps_to_range_constraints() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        self::assertSame(1, $schema->schema['properties']['rating']['minimum']);
        self::assertSame(10, $schema->schema['properties']['rating']['maximum']);
    }

    public function test_invalid_payload_fails_validation() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        $result = SchemaGeneration::validatePayload(
            ['name' => 't', 'email' => 'not-an-email', 'age' => 25, 'code' => 'ABC', 'rating' => 5],
            $schema,
        );

        self::assertFalse($result->valid);
        self::assertNotEmpty($result->errors);
    }

    public function test_valid_payload_passes_validation() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        $result = SchemaGeneration::validatePayload(
            ['name' => 'test user', 'email' => 'test@example.com', 'age' => 25, 'code' => 'ABC', 'rating' => 5],
            $schema,
        );

        self::assertTrue($result->valid);
        self::assertEmpty($result->errors);
    }

    public function test_missing_required_fields_fail_validation() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);

        $result = SchemaGeneration::validatePayload([], $schema);

        self::assertFalse($result->valid);
        self::assertContains("Required field 'name' is missing.", $result->errors);
        self::assertContains("Required field 'email' is missing.", $result->errors);
    }

    public function test_request_schema_generation() : void
    {
        $schema = SchemaGeneration::request(TestDataObject::class);

        self::assertArrayHasKey('properties', $schema->schema);
        self::assertArrayHasKey('required', $schema->schema);
    }

    public function test_response_schema_generation() : void
    {
        $schema = SchemaGeneration::response(TestDataObject::class);

        self::assertArrayHasKey('properties', $schema->schema);
    }

    public function test_schema_to_json_produces_valid_json() : void
    {
        $schema = SchemaGeneration::fromDataObject(TestDataObject::class);
        $json = $schema->toJson();

        $decoded = json_decode($json, true);

        self::assertIsArray($decoded);
        self::assertArrayHasKey('properties', $decoded);
    }
}

final class TestDataObject extends DataObject
{
    #[Required]
    #[StringType]
    #[Min(min: 2)]
    #[Max(max: 255)]
    public string $name;

    #[Required]
    #[Email]
    public string $email;

    #[IntegerType]
    #[Min(min: 0)]
    #[Max(max: 200)]
    public int $age;

    #[StringType]
    #[RegexPattern(pattern: '/^[A-Za-z]+$/')]
    public string $code;

    #[IntegerType]
    #[Between(min: 1, max: 10)]
    public int $rating;

    #[Hidden]
    public string $secret;
}
