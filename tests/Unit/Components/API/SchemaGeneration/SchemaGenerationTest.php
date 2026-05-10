<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\SchemaGeneration;

use Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertDataObjectShapeToJsonSchema\ConvertDataObjectShapeToJsonSchema;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ReadDataObjectShape\ReadDataObjectShape;
use Avax\Components\API\SchemaGeneration\System\Configuration\BuildSchemaGeneration;
use Avax\Components\API\SchemaGeneration\System\Configuration\SchemaGenerationAssembly;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateSchemaFromDataObject\GenerateSchemaFromDataObject;
use Avax\Components\API\SchemaGeneration\System\Flows\ValidatePayloadAgainstSchema\ValidatePayloadAgainstSchema;
use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\API\SchemaGeneration\System\Foundation\PayloadValidationResult;
use Avax\Components\API\SchemaGeneration\System\PublicSurface\SchemaGeneration;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Min;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use PHPUnit\Framework\TestCase;

final class SchemaGenerationTest extends TestCase
{
    public function test_from_data_object_class_returns_json_schema_document() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertInstanceOf(JsonSchemaDocument::class, $schema);
        $this->assertSame(SimpleUserRequest::class, $schema->schemaId);
    }

    public function test_from_data_object_instance_returns_json_schema_document() : void
    {
        $instance       = new SimpleUserInstance();
        $instance->name = 'AvaX';
        $instance->age  = 5;

        $schema = SchemaGeneration::fromDataObject(dataObject: $instance);

        $this->assertInstanceOf(JsonSchemaDocument::class, $schema);
        $this->assertSame(SimpleUserInstance::class, $schema->schemaId);
    }

    public function test_schema_document_has_object_type_and_properties() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertSame('object', $schema->schema['type']);
        $this->assertArrayHasKey('properties', $schema->schema);
        $this->assertArrayHasKey('$schema', $schema->schema);
        $this->assertSame('https://json-schema.org/draft/2020-12/schema', $schema->schema['$schema']);
    }

    public function test_required_fields_are_listed_in_schema() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertArrayHasKey('required', $schema->schema);
        $this->assertContains('name', $schema->schema['required']);
        $this->assertContains('email', $schema->schema['required']);
        $this->assertNotContains('nickname', $schema->schema['required']);
    }

    public function test_string_type_maps_to_string_json_type() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertSame('string', $schema->schema['properties']['name']['type']);
        $this->assertSame('string', $schema->schema['properties']['email']['type']);
    }

    public function test_integer_type_maps_to_integer_json_type() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertSame('integer', $schema->schema['properties']['age']['type']);
    }

    public function test_email_attribute_maps_to_format_email() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertArrayHasKey('format', $schema->schema['properties']['email']);
        $this->assertSame('email', $schema->schema['properties']['email']['format']);
    }

    public function test_hidden_fields_are_excluded_from_schema() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: UserWithHiddenField::class);

        $this->assertArrayNotHasKey('secretToken', $schema->schema['properties']);
        $this->assertArrayHasKey('username', $schema->schema['properties']);
    }

    public function test_min_attribute_maps_to_min_length_for_strings() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertSame(2, $schema->schema['properties']['name']['minLength']);
    }

    public function test_request_method_delegates_to_generate_request_flow() : void
    {
        $schema = SchemaGeneration::request(requestClass: SimpleUserRequest::class);

        $this->assertInstanceOf(JsonSchemaDocument::class, $schema);
        $this->assertArrayHasKey('properties', $schema->schema);
        $this->assertArrayHasKey('name', $schema->schema['properties']);
    }

    public function test_response_method_excludes_hidden_fields() : void
    {
        $schema = SchemaGeneration::response(responseClass: UserWithHiddenField::class);

        $this->assertArrayNotHasKey('secretToken', $schema->schema['properties']);
        $this->assertArrayHasKey('username', $schema->schema['properties']);
    }

    public function test_validate_payload_returns_valid_result_for_correct_payload() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $result = SchemaGeneration::validatePayload(
            payload: ['name' => 'AvaX', 'email' => 'test@example.com', 'age' => 5, 'nickname' => 'ax'],
            schema : $schema,
        );

        $this->assertInstanceOf(PayloadValidationResult::class, $result);
        $this->assertTrue($result->valid);
        $this->assertEmpty($result->errors);
    }

    public function test_validate_payload_returns_invalid_result_for_missing_required() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $result = SchemaGeneration::validatePayload(payload: [], schema: $schema);

        $this->assertFalse($result->valid);
        $this->assertNotEmpty($result->errors);
        $this->assertContains("Required field 'name' is missing.", $result->errors);
        $this->assertContains("Required field 'email' is missing.", $result->errors);
    }

    public function test_validate_payload_returns_invalid_for_wrong_type() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $result = SchemaGeneration::validatePayload(
            payload: ['name' => 123, 'email' => 'test@example.com'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'name' must be of type string.", $result->errors);
    }

    public function test_validate_payload_checks_min_length_constraint() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $result = SchemaGeneration::validatePayload(
            payload: ['name' => 'A', 'email' => 'test@example.com'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'name' must be at least 2 characters.", $result->errors);
    }

    public function test_set_assembly_allows_custom_assembly_injection() : void
    {
        $customAssembly = BuildSchemaGeneration::make();
        SchemaGeneration::setAssembly(assembly: $customAssembly);

        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertInstanceOf(JsonSchemaDocument::class, $schema);
    }

    public function test_set_assembly_null_resets_to_default() : void
    {
        SchemaGeneration::setAssembly(assembly: null);

        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertInstanceOf(JsonSchemaDocument::class, $schema);
    }

    public function test_optional_field_is_not_in_required_list() : void
    {
        $schema = SchemaGeneration::fromDataObject(dataObject: SimpleUserRequest::class);

        $this->assertArrayHasKey('properties', $schema->schema);
        $this->assertArrayHasKey('nickname', $schema->schema['properties']);

        if (isset($schema->schema['required'])) {
            $this->assertNotContains('nickname', $schema->schema['required']);
        }
    }

    protected function tearDown() : void
    {
        SchemaGeneration::setAssembly(assembly: null);
    }
}

// -- Test DataObjects --

final class SimpleUserRequest extends DataObject
{
    #[Required]
    #[StringType]
    #[Min(min: 2)]
    public string $name;

    #[Required]
    #[Email]
    public string $email;

    #[Optional]
    #[IntegerType]
    public ?int $age = null;

    #[Optional]
    #[StringType]
    public ?string $nickname = null;
}

final class SimpleUserInstance extends DataObject
{
    public string $name;
    public int    $age;
}

final class UserWithHiddenField extends DataObject
{
    #[Required]
    #[StringType]
    public string $username;

    #[Hidden]
    public string $secretToken;
}
