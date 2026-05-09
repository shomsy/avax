<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Flows\ValidatePayloadAgainstSchema;

use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\API\SchemaGeneration\System\Foundation\PayloadValidationResult;

/**
 * Validates a payload array against a JSON Schema document.
 * Implements a focused subset of JSON Schema validation rules.
 */
final readonly class ValidatePayloadAgainstSchema
{
    /**
     * @param array<string, mixed> $payload
     */
    public function execute(array $payload, JsonSchemaDocument $schema) : PayloadValidationResult
    {
        $errors = [];
        $schemaArray = $schema->schema;

        if (($schemaArray['type'] ?? null) === 'object') {
            $properties = $schemaArray['properties'] ?? [];
            $required = $schemaArray['required'] ?? [];

            foreach ($required as $fieldName) {
                if (!array_key_exists(key: $fieldName, array: $payload)) {
                    $errors[] = "Required field '{$fieldName}' is missing.";
                }
            }

            foreach ($payload as $fieldName => $value) {
                if (!isset($properties[$fieldName])) {
                    continue;
                }

                $fieldSchema = $properties[$fieldName];
                $fieldErrors = $this->validateField(value: $value, schema: $fieldSchema, path: $fieldName);
                $errors = [...$errors, ...$fieldErrors];
            }
        }

        return $errors === []
            ? PayloadValidationResult::valid()
            : PayloadValidationResult::invalid($errors);
    }

    /**
     * @param array<string, mixed> $schema
     * @return list<string>
     */
    private function validateField(mixed $value, array $schema, string $path) : array
    {
        $errors = [];

        if (isset($schema['type']) && $value !== null) {
            $typeValid = match ($schema['type']) {
                'string' => is_string(value: $value),
                'integer' => is_int(value: $value),
                'number' => is_int(value: $value) || is_float(value: $value),
                'boolean' => is_bool(value: $value),
                'object' => is_array(value: $value),
                'array' => is_array(value: $value),
                default => true,
            };

            if (! $typeValid) {
                $errors[] = "Field '{$path}' must be of type {$schema['type']}.";
            }
        }

        if (is_string(value: $value)) {
            if (isset($schema['minLength']) && mb_strlen(string: $value) < $schema['minLength']) {
                $errors[] = "Field '{$path}' must be at least {$schema['minLength']} characters.";
            }

            if (isset($schema['maxLength']) && mb_strlen(string: $value) > $schema['maxLength']) {
                $errors[] = "Field '{$path}' must be at most {$schema['maxLength']} characters.";
            }

            if (isset($schema['pattern']) && preg_match(pattern: $schema['pattern'], subject: $value) !== 1) {
                $errors[] = "Field '{$path}' does not match the required pattern.";
            }

            if (($schema['format'] ?? null) === 'email' && !filter_var(value: $value, filter: FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Field '{$path}' must be a valid email address.";
            }
        }

        if (is_int(value: $value) || is_float(value: $value)) {
            if (isset($schema['minimum']) && $value < $schema['minimum']) {
                $errors[] = "Field '{$path}' must be at least {$schema['minimum']}.";
            }

            if (isset($schema['maximum']) && $value > $schema['maximum']) {
                $errors[] = "Field '{$path}' must be at most {$schema['maximum']}.";
            }
        }

        return $errors;
    }
}
