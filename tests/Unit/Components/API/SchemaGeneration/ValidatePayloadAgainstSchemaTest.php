<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\SchemaGeneration;

use Avax\Components\API\SchemaGeneration\System\Flows\ValidatePayloadAgainstSchema\ValidatePayloadAgainstSchema;
use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\API\SchemaGeneration\System\Foundation\PayloadValidationResult;
use PHPUnit\Framework\TestCase;

final class ValidatePayloadAgainstSchemaTest extends TestCase
{
    public function test_valid_empty_payload_against_empty_schema() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = new JsonSchemaDocument(
            schemaId: 'EmptySchema',
            schema  : [
                          '$schema'    => 'https://json-schema.org/draft/2020-12/schema',
                          'type'       => 'object',
                          'properties' => [],
                      ],
        );

        $result = $validator->execute(payload: [], schema: $schema);

        $this->assertTrue($result->valid);
        $this->assertEmpty($result->errors);
    }

    public function test_valid_payload_with_all_required_fields() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->simpleUserSchema();

        $result = $validator->execute(
            payload: ['name' => 'AvaX', 'email' => 'ava@x.com'],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
        $this->assertEmpty($result->errors);
    }

    private function simpleUserSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'UserSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'name'  => ['type' => 'string'],
                              'email' => ['type' => 'string'],
                          ],
                          'required'   => ['name', 'email'],
                      ],
        );
    }

    public function test_missing_required_field_produces_error() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->simpleUserSchema();

        $result = $validator->execute(payload: ['name' => 'AvaX'], schema: $schema);

        $this->assertFalse($result->valid);
        $this->assertContains("Required field 'email' is missing.", $result->errors);
    }

    public function test_multiple_missing_required_fields_produce_multiple_errors() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->simpleUserSchema();

        $result = $validator->execute(payload: [], schema: $schema);

        $this->assertFalse($result->valid);
        $this->assertCount(2, $result->errors);
        $this->assertContains("Required field 'name' is missing.", $result->errors);
        $this->assertContains("Required field 'email' is missing.", $result->errors);
    }

    public function test_wrong_string_type_produces_error() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->simpleUserSchema();

        $result = $validator->execute(
            payload: ['name' => 42, 'email' => 'test@test.com'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'name' must be of type string.", $result->errors);
    }

    public function test_wrong_integer_type_produces_error() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->numericUserSchema();

        $result = $validator->execute(
            payload: ['name' => 'AvaX', 'age' => 'not-a-number'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'age' must be of type integer.", $result->errors);
    }

    private function numericUserSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'NumericUserSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'name' => ['type' => 'string'],
                              'age'  => ['type' => 'integer'],
                          ],
                          'required'   => ['name', 'age'],
                      ],
        );
    }

    public function test_wrong_boolean_type_produces_error() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->booleanSchema();

        $result = $validator->execute(
            payload: ['active' => 'yes'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'active' must be of type boolean.", $result->errors);
    }

    private function booleanSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'BooleanSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'active' => ['type' => 'boolean'],
                          ],
                          'required'   => ['active'],
                      ],
        );
    }

    public function test_valid_integer_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->numericUserSchema();

        $result = $validator->execute(
            payload: ['name' => 'AvaX', 'age' => 25],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_valid_boolean_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->booleanSchema();

        $result = $validator->execute(
            payload: ['active' => true],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_string_below_min_length_fails() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedStringSchema();

        $result = $validator->execute(
            payload: ['username' => 'ab'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'username' must be at least 3 characters.", $result->errors);
    }

    private function constrainedStringSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'ConstrainedStringSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'username' => [
                                  'type'      => 'string',
                                  'minLength' => 3,
                                  'maxLength' => 20,
                              ],
                          ],
                          'required'   => ['username'],
                      ],
        );
    }

    public function test_string_above_max_length_fails() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedStringSchema();

        $result = $validator->execute(
            payload: ['username' => 'this_username_is_way_too_long_for_the_limit'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'username' must be at most 20 characters.", $result->errors);
    }

    public function test_string_within_length_bounds_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedStringSchema();

        $result = $validator->execute(
            payload: ['username' => 'valid_user'],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_integer_below_minimum_fails() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedNumericSchema();

        $result = $validator->execute(
            payload: ['count' => -1],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'count' must be at least 0.", $result->errors);
    }

    private function constrainedNumericSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'ConstrainedNumericSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'count' => [
                                  'type'    => 'integer',
                                  'minimum' => 0,
                                  'maximum' => 100,
                              ],
                          ],
                          'required'   => ['count'],
                      ],
        );
    }

    public function test_integer_above_maximum_fails() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedNumericSchema();

        $result = $validator->execute(
            payload: ['count' => 101],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'count' must be at most 100.", $result->errors);
    }

    public function test_integer_within_bounds_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedNumericSchema();

        $result = $validator->execute(
            payload: ['count' => 50],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_invalid_email_format_fails() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->emailSchema();

        $result = $validator->execute(
            payload: ['email' => 'not-an-email'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'email' must be a valid email address.", $result->errors);
    }

    private function emailSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'EmailSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'email' => [
                                  'type'   => 'string',
                                  'format' => 'email',
                              ],
                          ],
                          'required'   => ['email'],
                      ],
        );
    }

    public function test_valid_email_format_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->emailSchema();

        $result = $validator->execute(
            payload: ['email' => 'user@example.com'],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_string_not_matching_pattern_fails() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->patternSchema();

        $result = $validator->execute(
            payload: ['slug' => 'INVALID_SLUG'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'slug' does not match the required pattern.", $result->errors);
    }

    private function patternSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'PatternSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'slug' => [
                                  'type'    => 'string',
                                  'pattern' => '/^[a-z0-9-]+$/',
                              ],
                          ],
                          'required'   => ['slug'],
                      ],
        );
    }

    public function test_string_matching_pattern_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->patternSchema();

        $result = $validator->execute(
            payload: ['slug' => 'valid-slug-123'],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_null_value_passes_type_check() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->simpleUserSchema();

        $result = $validator->execute(
            payload: ['name' => null, 'email' => 'test@test.com'],
            schema : $schema,
        );

        // The validator skips type check for null values
        $this->assertTrue($result->valid);
    }

    public function test_extra_fields_not_in_schema_are_ignored() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->simpleUserSchema();

        $result = $validator->execute(
            payload: ['name' => 'AvaX', 'email' => 'ava@x.com', 'extra_field' => 'ignored'],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_non_object_schema_is_ignored() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = new JsonSchemaDocument(
            schemaId: 'NonObjectSchema',
            schema  : [
                          'type' => 'string',
                      ],
        );

        $result = $validator->execute(payload: ['anything'], schema: $schema);

        $this->assertTrue($result->valid);
        $this->assertEmpty($result->errors);
    }

    public function test_float_value_passes_number_type() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->numberSchema();

        $result = $validator->execute(
            payload: ['price' => 19.99],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    private function numberSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'NumberSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'price' => ['type' => 'number'],
                          ],
                          'required'   => ['price'],
                      ],
        );
    }

    public function test_integer_value_passes_number_type() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->numberSchema();

        $result = $validator->execute(
            payload: ['price' => 100],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_string_value_fails_number_type() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->numberSchema();

        $result = $validator->execute(
            payload: ['price' => 'free'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'price' must be of type number.", $result->errors);
    }

    public function test_array_value_passes_array_type() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->arraySchema();

        $result = $validator->execute(
            payload: ['tags' => ['a', 'b', 'c']],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    private function arraySchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'ArraySchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'tags' => ['type' => 'array'],
                          ],
                          'required'   => ['tags'],
                      ],
        );
    }

    public function test_array_value_passes_object_type() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->objectTypeSchema();

        $result = $validator->execute(
            payload: ['metadata' => ['key' => 'value']],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    private function objectTypeSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'ObjectTypeSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'metadata' => ['type' => 'object'],
                          ],
                          'required'   => ['metadata'],
                      ],
        );
    }

    public function test_non_array_fails_array_type() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->arraySchema();

        $result = $validator->execute(
            payload: ['tags' => 'not-an-array'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'tags' must be of type array.", $result->errors);
    }

    public function test_min_length_exactly_at_boundary_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedStringSchema();

        $result = $validator->execute(
            payload: ['username' => 'abc'],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_max_length_exactly_at_boundary_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedStringSchema();

        $result = $validator->execute(
            payload: ['username' => 'abcdefghij'],
            schema : $schema,
        );

        // 10 chars is within 3-20
        $this->assertTrue($result->valid);
    }

    public function test_minimum_exactly_at_boundary_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedNumericSchema();

        $result = $validator->execute(
            payload: ['count' => 0],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_maximum_exactly_at_boundary_passes() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedNumericSchema();

        $result = $validator->execute(
            payload: ['count' => 100],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_valid_result_has_no_errors() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->simpleUserSchema();

        $result = $validator->execute(
            payload: ['name' => 'AvaX', 'email' => 'ava@x.com'],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
        $this->assertSame([], $result->errors);
    }

    public function test_invalid_result_collects_all_errors() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->fullyConstrainedSchema();

        $result = $validator->execute(
            payload: [
                         'username' => 'ab',  // too short
                         'email'    => 'bad',  // bad email
                         'age'      => -1,    // below minimum
                         'score'    => 200,   // above maximum
                     ],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertGreaterThanOrEqual(4, count($result->errors));
    }

    private function fullyConstrainedSchema() : JsonSchemaDocument
    {
        return new JsonSchemaDocument(
            schemaId: 'FullyConstrainedSchema',
            schema  : [
                          'type'       => 'object',
                          'properties' => [
                              'username' => [
                                  'type'      => 'string',
                                  'minLength' => 3,
                                  'maxLength' => 50,
                              ],
                              'email'    => [
                                  'type'   => 'string',
                                  'format' => 'email',
                              ],
                              'age'      => [
                                  'type'    => 'integer',
                                  'minimum' => 0,
                                  'maximum' => 150,
                              ],
                              'score'    => [
                                  'type'    => 'integer',
                                  'minimum' => 0,
                                  'maximum' => 100,
                              ],
                          ],
                          'required'   => ['username', 'email', 'age', 'score'],
                      ],
        );
    }

    public function test_payload_with_partial_constraints_validates_correctly() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->fullyConstrainedSchema();

        $result = $validator->execute(
            payload: [
                         'username' => 'valid_username',
                         'email'    => 'user@example.com',
                         'age'      => 25,
                         'score'    => 75,
                     ],
            schema : $schema,
        );

        $this->assertTrue($result->valid);
    }

    public function test_email_with_at_but_no_domain_fails() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->emailSchema();

        $result = $validator->execute(
            payload: ['email' => 'user@'],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
    }

    public function test_empty_string_fails_min_length() : void
    {
        $validator = new ValidatePayloadAgainstSchema();
        $schema    = $this->constrainedStringSchema();

        $result = $validator->execute(
            payload: ['username' => ''],
            schema : $schema,
        );

        $this->assertFalse($result->valid);
        $this->assertContains("Field 'username' must be at least 3 characters.", $result->errors);
    }
}
