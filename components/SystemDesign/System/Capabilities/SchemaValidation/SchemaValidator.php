<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\SchemaValidation;

use Avax\Components\SystemDesign\System\Foundation\Failure\SchemaParseException;

/**
 * Validates a parsed config array against a V3 schema definition.
 *
 * Schema format (PHP array or parsed from YAML schema file):
 * - Top-level keys define expected sections.
 * - Each section may define: required, types, constraints, optional.
 *
 * @experimental V3 labs
 */
final class SchemaValidator
{
    private NativeYamlParser $yamlParser;

    public function __construct(NativeYamlParser|null $yamlParser = null)
    {
        $this->yamlParser = $yamlParser ?? new NativeYamlParser();
    }

    public function yamlParser() : NativeYamlParser
    {
        return $this->yamlParser;
    }

    /**
     * Validate a target file against a schema file.
     *
     * @param string $schemaPath Path to schema YAML file.
     * @param string $targetPath Path to target config YAML file.
     *
     * @throws SchemaParseException
     */
    public function validateFile(string $schemaPath, string $targetPath) : SchemaValidationResult
    {
        $schema = $this->yamlParser->parseFile($schemaPath);
        $target = $this->yamlParser->parseFile($targetPath);

        return $this->validate($schema, $target, basename($schemaPath), $targetPath);
    }

    /**
     * Validate a config array against a schema array.
     *
     * @param array<int|string, mixed> $schema     Parsed schema definition.
     * @param array<int|string, mixed> $config     Parsed config to validate.
     * @param string                   $schemaName Schema name for result reporting.
     * @param string                   $targetName Target name for result reporting.
     */
    public function validate(
        array  $schema,
        array  $config,
        string $schemaName = 'unknown',
        string $targetName = 'unknown',
    ) : SchemaValidationResult
    {
        $errors = [];

        // Schema with single top-level key (e.g., "sections:" or "assertions:" or "scenarios:")
        $topKeys = array_keys($schema);
        if (count($topKeys) === 1) {
            $sectionKey   = $topKeys[0];
            $sectionDef   = $schema[$sectionKey];
            $sectionValue = $config[$sectionKey] ?? null;

            // "sections:" is a wrapper — each key inside is a real section definition
            // "assertions:" / "scenarios:" are array containers — validate as array
            if ($sectionKey === 'sections') {
                // Each key in sectionDef is a section name (traffic, storage, etc.)
                foreach ($sectionDef as $name => $def) {
                    if (! is_array($def)) {
                        continue;
                    }
                    $value  = $config[$name] ?? null;
                    $errors = array_merge(
                        $errors,
                        $this->validateSection($def, $value, (string) $name),
                    );
                }

                if ($errors !== []) {
                    return SchemaValidationResult::fail($errors, $schemaName, $targetName);
                }

                return SchemaValidationResult::pass($schemaName, $targetName);
            }

            if ($sectionValue === null) {
                $errors[] = "Missing required top-level section: {$sectionKey}";

                return SchemaValidationResult::fail($errors, $schemaName, $targetName);
            }

            $errors = array_merge(
                $errors,
                $this->validateSection($sectionDef, $sectionValue, (string) $sectionKey),
            );
        } else {
            // Multi-section schema (legacy format)
            foreach ($topKeys as $key) {
                $def   = $schema[$key];
                $value = $config[$key] ?? null;

                if (! isset($def['required']) || ! is_array($def['required'])) {
                    continue;
                }

                if ($value === null) {
                    $errors[] = "Missing required section: {$key}";
                    continue;
                }

                $errors = array_merge(
                    $errors,
                    $this->validateSection($def, $value, (string) $key),
                );
            }
        }

        if ($errors !== []) {
            return SchemaValidationResult::fail($errors, $schemaName, $targetName);
        }

        return SchemaValidationResult::pass($schemaName, $targetName);
    }

    /**
     * @param array<string, mixed> $def
     * @param mixed                $value
     *
     * @return list<string>
     */
    private function validateSection(array $def, mixed $value, string $sectionName) : array
    {
        $errors = [];

        // Section with no required fields — check if it's optional (has only optional/ description)
        $required    = $def['required'] ?? null;
        $hasRequired = is_array($required) && $required !== [];

        // If section has no required fields and value is null, skip (optional section)
        if (! $hasRequired && $value === null) {
            return $errors;
        }

        // Simple scalar section (e.g., system: string)
        if (isset($def['type']) && $def['type'] !== 'object' && $def['type'] !== 'array') {
            if ($value === null) {
                // Already handled optional case above; if we reach here with null, it's required
                $errors[] = "Missing required section: {$sectionName}";

                return $errors;
            }

            if (! $this->matchesType($value, $def['type'])) {
                $errors[] = "{$sectionName} must be of type {$def['type']}, got " . gettype($value);
            }

            // Check constraints on scalar
            if (isset($def['constraint'])) {
                $constraintErrors = $this->checkConstraint($value, $def['constraint'], $sectionName);
                $errors           = array_merge($errors, $constraintErrors);
            }

            return $errors;
        }

        // Array type with item schema (for scenarios and assertions)
        if (($def['type'] ?? '') === 'array' && isset($def['item_schema'])) {
            if (! is_array($value)) {
                $errors[] = "{$sectionName} must be an array.";

                return $errors;
            }

            if (isset($def['min_items']) && count($value) < $def['min_items']) {
                $errors[] = "{$sectionName} must have at least {$def['min_items']} item(s).";
            }

            foreach ($value as $index => $item) {
                $itemErrors = $this->validateItemSchema($def['item_schema'], $item, "{$sectionName}[{$index}]");
                $errors     = array_merge($errors, $itemErrors);
            }

            return $errors;
        }

        // Object type with optional properties (e.g., external_dependencies)
        if (($def['type'] ?? '') === 'object') {
            if ($value === null) {
                return $errors; // Optional object section
            }
            if (! is_array($value)) {
                $errors[] = "{$sectionName} must be a mapping.";

                return $errors;
            }

            $optional = $def['optional'] ?? [];
            foreach ($optional as $optKey => $optDef) {
                if (! array_key_exists($optKey, $value)) {
                    continue;
                }
                if (is_array($optDef) && isset($optDef['properties'])) {
                    $subErrors = $this->validateSubObject($optDef, $value[$optKey], "{$sectionName}.{$optKey}");
                    $errors    = array_merge($errors, $subErrors);
                }
            }

            return $errors;
        }

        // Sections map (capacity-style: each key is a section with required fields)
        if (! is_array($value)) {
            $errors[] = "{$sectionName} must be a mapping.";

            return $errors;
        }

        $required    = $def['required'] ?? [];
        $types       = $def['types'] ?? [];
        $constraints = $def['constraints'] ?? [];
        $optional    = $def['optional'] ?? [];

        // Check required fields
        foreach ($required as $field) {
            if (! array_key_exists($field, $value)) {
                $errors[] = "Missing required field: {$sectionName}.{$field}";
            }
        }

        // Check types
        foreach ($types as $field => $expectedType) {
            if (! array_key_exists($field, $value)) {
                continue;
            }

            $actualValue = $value[$field];
            if (! $this->matchesType($actualValue, $expectedType)) {
                $errors[] = "Field {$sectionName}.{$field} must be of type {$expectedType}, got " . gettype($actualValue);
            }
        }

        // Check constraints
        foreach ($constraints as $field => $constraint) {
            if (! array_key_exists($field, $value)) {
                continue;
            }

            $constraintErrors = $this->checkConstraint($value[$field], $constraint, "{$sectionName}.{$field}");
            $errors           = array_merge($errors, $constraintErrors);
        }

        // Validate optional sub-objects
        if (is_array($optional)) {
            foreach ($optional as $optKey => $optDef) {
                if (! array_key_exists($optKey, $value)) {
                    continue;
                }

                if (is_array($optDef) && isset($optDef['properties'])) {
                    $subErrors = $this->validateSubObject($optDef, $value[$optKey], "{$sectionName}.{$optKey}");
                    $errors    = array_merge($errors, $subErrors);
                }
            }
        }

        return $errors;
    }

    private function matchesType(mixed $value, string $expectedType) : bool
    {
        return match ($expectedType) {
            'string' => is_string($value),
            'int'    => is_int($value),
            'float'  => is_numeric($value),
            'bool'   => is_bool($value),
            'array'  => is_array($value),
            'object' => is_array($value),
            'mixed'  => true,
            default  => true,
        };
    }

    /**
     * @return list<string>
     */
    private function checkConstraint(mixed $value, string $constraint, string $field) : array
    {
        $errors = [];

        // not_empty
        if ($constraint === 'not_empty') {
            if (is_string($value) && $value === '') {
                $errors[] = "Field {$field} must not be empty.";
            }
        }

        // one_of:...
        if (str_starts_with($constraint, 'one_of:')) {
            $allowed = explode(',', substr($constraint, 7));
            $allowed = array_map(trim(...), $allowed);
            if (is_string($value) && ! in_array($value, $allowed, true)) {
                $errors[] = "Field {$field} must be one of: " . implode(', ', $allowed) . ". Got: {$value}";
            }
        }

        // Comparison constraints for numeric values
        if (is_numeric($value)) {
            $num = (float) $value;

            if (str_starts_with($constraint, '>= ')) {
                $threshold = (float) substr($constraint, 3);
                if ($num < $threshold) {
                    $errors[] = "Field {$field} must be >= {$threshold}. Got: {$value}";
                }
            } elseif (str_starts_with($constraint, '> ')) {
                $threshold = (float) substr($constraint, 2);
                if ($num <= $threshold) {
                    $errors[] = "Field {$field} must be > {$threshold}. Got: {$value}";
                }
            } elseif (str_starts_with($constraint, '<= ')) {
                $threshold = (float) substr($constraint, 3);
                if ($num > $threshold) {
                    $errors[] = "Field {$field} must be <= {$threshold}. Got: {$value}";
                }
            } elseif (str_starts_with($constraint, '< ')) {
                $threshold = (float) substr($constraint, 2);
                if ($num >= $threshold) {
                    $errors[] = "Field {$field} must be < {$threshold}. Got: {$value}";
                }
            } elseif (str_contains($constraint, '&&')) {
                // Compound constraint like ">= 0 && <= 1"
                $parts = explode('&&', $constraint);
                foreach ($parts as $part) {
                    $part      = trim($part);
                    $subErrors = $this->checkConstraint($value, $part, $field);
                    $errors    = array_merge($errors, $subErrors);
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $itemSchema
     * @param mixed                $item
     *
     * @return list<string>
     */
    private function validateItemSchema(array $itemSchema, mixed $item, string $path) : array
    {
        $errors = [];

        if (! is_array($item)) {
            $errors[] = "{$path} must be an object.";

            return $errors;
        }

        $required    = $itemSchema['required'] ?? [];
        $types       = $itemSchema['types'] ?? [];
        $constraints = $itemSchema['constraints'] ?? [];

        foreach ($required as $field) {
            if (! array_key_exists($field, $item)) {
                $errors[] = "Missing required field: {$path}.{$field}";
            }
        }

        foreach ($types as $field => $expectedType) {
            if (! array_key_exists($field, $item)) {
                continue;
            }

            $actualValue = $item[$field];
            if (! $this->matchesType($actualValue, $expectedType)) {
                $errors[] = "Field {$path}.{$field} must be of type {$expectedType}, got " . gettype($actualValue);
            }
        }

        foreach ($constraints as $field => $constraint) {
            if (! array_key_exists($field, $item)) {
                continue;
            }

            $constraintErrors = $this->checkConstraint($item[$field], $constraint, "{$path}.{$field}");
            $errors           = array_merge($errors, $constraintErrors);
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $def
     * @param mixed                $value
     *
     * @return list<string>
     */
    private function validateSubObject(array $def, mixed $value, string $path) : array
    {
        $errors = [];

        if (! is_array($value)) {
            return $errors;
        }

        $properties  = $def['properties'] ?? [];
        $constraints = $def['constraints'] ?? [];

        foreach ($properties as $prop => $propType) {
            if (! array_key_exists($prop, $value)) {
                continue;
            }

            if (! $this->matchesType($value[$prop], $propType)) {
                $errors[] = "Field {$path}.{$prop} must be of type {$propType}, got " . gettype($value[$prop]);
            }
        }

        foreach ($constraints as $prop => $constraint) {
            if (! array_key_exists($prop, $value)) {
                continue;
            }

            $constraintErrors = $this->checkConstraint($value[$prop], $constraint, "{$path}.{$prop}");
            $errors           = array_merge($errors, $constraintErrors);
        }

        return $errors;
    }
}
