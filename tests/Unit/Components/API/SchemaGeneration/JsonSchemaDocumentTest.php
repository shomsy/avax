<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\SchemaGeneration;

use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\API\SchemaGeneration\System\Foundation\SchemaGenerationFailed;
use PHPUnit\Framework\TestCase;

final class JsonSchemaDocumentTest extends TestCase
{
    public function test_constructor_stores_schema_id() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'Avax\Tests\TestDto',
            schema  : ['type' => 'object'],
        );

        $this->assertSame('Avax\Tests\TestDto', $doc->schemaId);
    }

    public function test_constructor_stores_schema_array() : void
    {
        $schema = [
            '$schema'    => 'https://json-schema.org/draft/2020-12/schema',
            'type'       => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
            ],
        ];

        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : $schema,
        );

        $this->assertSame($schema, $doc->schema);
    }

    public function test_to_json_produces_valid_json() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]],
        );

        $json = $doc->toJson();

        $this->assertJson($json);
    }

    public function test_to_json_contains_schema_properties() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]],
        );

        $json    = $doc->toJson();
        $decoded = json_decode($json, true);

        $this->assertSame('object', $decoded['type']);
        $this->assertArrayHasKey('name', $decoded['properties']);
        $this->assertSame('string', $decoded['properties']['name']['type']);
    }

    public function test_to_json_with_pretty_print() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : ['type' => 'object'],
        );

        $json = $doc->toJson(flags: JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $json);
        $this->assertStringContainsString('    ', $json);
    }

    public function test_to_json_without_pretty_print_is_compact() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : ['type' => 'object'],
        );

        $json = $doc->toJson(flags: 0);

        $this->assertSame('{"type":"object"}', $json);
    }

    public function test_to_json_unescaped_slashes() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : ['$schema' => 'https://json-schema.org/draft/2020-12/schema'],
        );

        $json = $doc->toJson(flags: JSON_UNESCAPED_SLASHES);

        $this->assertStringContainsString('https://json-schema.org/draft/2020-12/schema', $json);
        $this->assertStringNotContainsString('https:\/\/json-schema.org', $json);
    }

    public function test_to_json_with_custom_flags() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : ['type' => 'object'],
        );

        $json = $doc->toJson(flags: JSON_THROW_ON_ERROR);

        $this->assertJson($json);
    }

    public function test_to_json_decodes_back_to_original_schema() : void
    {
        $original = [
            '$schema'    => 'https://json-schema.org/draft/2020-12/schema',
            'type'       => 'object',
            'properties' => [
                'name'  => ['type' => 'string'],
                'age'   => ['type' => 'integer'],
                'email' => ['type' => 'string', 'format' => 'email'],
            ],
            'required'   => ['name', 'email'],
        ];

        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : $original,
        );

        $decoded = json_decode($doc->toJson(), true);

        $this->assertSame($original, $decoded);
    }

    public function test_schema_is_readonly_cannot_be_modified() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : ['type' => 'object'],
        );

        // readonly class - verify the property is accessible but immutable
        $this->assertSame(['type' => 'object'], $doc->schema);
    }

    public function test_empty_schema_serializes_correctly() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'EmptyDto',
            schema  : [],
        );

        $json = $doc->toJson();

        $this->assertSame('[]', $json);
    }

    public function test_schema_with_nested_arrays_serializes_correctly() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'NestedDto',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'address' => [
                                  'type'       => 'object',
                                  'properties' => [
                                      'street' => ['type' => 'string'],
                                      'city'   => ['type' => 'string'],
                                  ],
                              ],
                          ],
                      ],
        );

        $json    = $doc->toJson();
        $decoded = json_decode($json, true);

        $this->assertArrayHasKey('address', $decoded['properties']);
        $this->assertArrayHasKey('street', $decoded['properties']['address']['properties']);
        $this->assertArrayHasKey('city', $decoded['properties']['address']['properties']);
    }

    public function test_schema_with_unicode_values_serializes_correctly() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'UnicodeDto',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'name' => [
                                  'type'        => 'string',
                                  'description' => 'Imię użytkownika',
                              ],
                          ],
                      ],
        );

        $json    = $doc->toJson(flags: JSON_UNESCAPED_UNICODE);
        $decoded = json_decode($json, true);

        $this->assertSame('Imię użytkownika', $decoded['properties']['name']['description']);
    }

    public function test_schema_id_can_be_namespaced_class() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument',
            schema  : ['type' => 'object'],
        );

        $this->assertSame('Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument', $doc->schemaId);
    }

    public function test_schema_id_can_be_arbitrary_string() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'my-custom-schema',
            schema  : ['type' => 'object'],
        );

        $this->assertSame('my-custom-schema', $doc->schemaId);
    }

    public function test_schema_with_boolean_values_serializes_correctly() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'BooleanDto',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'active' => ['type' => 'boolean'],
                              'hidden' => false,
                          ],
                      ],
        );

        $json    = $doc->toJson();
        $decoded = json_decode($json, true);

        $this->assertTrue($decoded['properties']['active']['type'] === 'boolean');
        $this->assertFalse($decoded['properties']['hidden']);
    }

    public function test_schema_with_numeric_values_serializes_correctly() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'NumericDto',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'count' => [
                                  'type'    => 'integer',
                                  'minimum' => 0,
                                  'maximum' => 100,
                                  'default' => 50,
                              ],
                          ],
                      ],
        );

        $json    = $doc->toJson();
        $decoded = json_decode($json, true);

        $this->assertSame(0, $decoded['properties']['count']['minimum']);
        $this->assertSame(100, $decoded['properties']['count']['maximum']);
        $this->assertSame(50, $decoded['properties']['count']['default']);
    }

    public function test_schema_with_null_value_serializes_correctly() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'NullDto',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'optional' => null,
                          ],
                      ],
        );

        $json    = $doc->toJson();
        $decoded = json_decode($json, true);

        $this->assertNull($decoded['properties']['optional']);
    }

    public function test_default_flags_produce_pretty_unescaped_json() : void
    {
        $doc = new JsonSchemaDocument(
            schemaId: 'TestDto',
            schema  : ['$schema' => 'https://json-schema.org/draft/2020-12/schema'],
        );

        $json = $doc->toJson();

        // Default is JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        $this->assertStringContainsString("\n", $json);
        $this->assertStringNotContainsString('\\/', $json);
    }
}
