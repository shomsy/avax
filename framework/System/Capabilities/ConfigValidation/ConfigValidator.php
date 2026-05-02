<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ConfigValidation;

/**
 * Validates configuration against registered schemas.
 */
final class ConfigValidator
{
    /**
     * @var array<string, ConfigSchema>
     */
    private array $schemas = [];

    public function register(ConfigSchema $configSchema) : self
    {
        $this->schemas[$configSchema->name] = $configSchema;

        return $this;
    }

    /**
     * Validate all registered schemas against config sections.
     *
     * @param array<string, array<string, mixed>> $allConfig
     *
     * @return list<ConfigSchemaViolation>
     */
    public function validateAll(array $allConfig): array
    {
        $violations = [];

        foreach (array_keys($this->schemas) as $name) {
            $section    = $allConfig[$name] ?? [];
            $violations = [...$violations, ...$this->validate($name, $section)];
        }

        return $violations;
    }

    /**
     * Validate a config section against its schema.
     *
     * @param array<string, mixed> $config
     *
     * @return list<ConfigSchemaViolation>
     */
    public function validate(string $schemaName, array $config): array
    {
        if (! isset($this->schemas[$schemaName])) {
            return [new ConfigSchemaViolation(
                severity: ConfigSchemaViolation::SEVERITY_WARNING,
                key     : $schemaName,
                message : sprintf("No schema registered for '%s'", $schemaName),
            )];
        }

        $schema     = $this->schemas[$schemaName];
        $violations = [];

        foreach ($schema->fields as $fieldName => $field) {
            $dotKey = sprintf('%s.%s', $schemaName, $fieldName);
            $exists = array_key_exists($fieldName, $config);
            $value  = $exists ? $config[$fieldName] : null;

            if (! $exists) {
                if ($field->required) {
                    $violations[] = new ConfigSchemaViolation(
                        severity   : ConfigSchemaViolation::SEVERITY_ERROR,
                        key        : $dotKey,
                        message    : sprintf("Required config key '%s' is missing", $dotKey),
                        remediation: $field->description !== null
                                         ? sprintf('Set %s (%s)', $dotKey, $field->description)
                                         : sprintf('Set %s in config/%s.php or via environment variable', $dotKey, $schemaName),
                    );
                }

                continue;
            }

            $typeViolations = $this->validateType($dotKey, $value, $field);
            $violations     = [...$violations, ...$typeViolations];

            if ($field->allowed !== [] && ! in_array($value, $field->allowed, true)) {
                $violations[] = new ConfigSchemaViolation(
                    severity   : ConfigSchemaViolation::SEVERITY_ERROR,
                    key        : $dotKey,
                    message    : sprintf(
                        "Config '%s' has invalid value '%s'. Allowed: %s",
                        $dotKey,
                        var_export($value, true),
                        implode(', ', array_map(static fn (mixed $value): string => var_export($value, true), $field->allowed)),
                    ),
                    remediation: sprintf(
                        'Change %s to one of: %s',
                        $dotKey,
                        implode(', ', array_map(static fn (mixed $value): string => var_export($value, true), $field->allowed)),
                    ),
                );
            }
        }

        return $violations;
    }

    /**
     * @return list<ConfigSchemaViolation>
     */
    private function validateType(string $key, mixed $value, ConfigSchemaField $configSchemaField) : array
    {
        $violations = [];

        $isValid = match ($configSchemaField->type) {
            ConfigSchemaField::TYPE_STRING           => is_string($value),
            ConfigSchemaField::TYPE_NON_EMPTY_STRING => is_string($value) && $value !== '',
            ConfigSchemaField::TYPE_INT              => is_int($value),
            ConfigSchemaField::TYPE_FLOAT            => is_float($value) || is_int($value),
            ConfigSchemaField::TYPE_BOOL             => is_bool($value),
            ConfigSchemaField::TYPE_ARRAY            => is_array($value),
            ConfigSchemaField::TYPE_URL              => is_string($value) && filter_var($value, FILTER_VALIDATE_URL)   !== false,
            ConfigSchemaField::TYPE_EMAIL            => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            default                                  => true,
        };

        if (! $isValid) {
            $violations[] = new ConfigSchemaViolation(
                severity   : ConfigSchemaViolation::SEVERITY_ERROR,
                key        : $key,
                message    : sprintf(
                    "Config '%s' expected type '%s', got '%s'",
                    $key,
                    $configSchemaField->type,
                    get_debug_type($value),
                ),
                remediation: sprintf(
                    'Change %s to a %s value',
                    $key,
                    $configSchemaField->type,
                ),
            );
        }

        return $violations;
    }
}
