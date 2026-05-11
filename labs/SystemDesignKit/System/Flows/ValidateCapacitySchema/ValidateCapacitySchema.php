<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Flows\ValidateCapacitySchema;

use Avax\Labs\SystemDesignKit\System\Capabilities\SchemaValidation\SchemaValidationResult;
use Avax\Labs\SystemDesignKit\System\Capabilities\SchemaValidation\SchemaValidator;

/**
 * Validates a capacity.yaml file against the capacity schema.
 *
 * @experimental V3 labs
 */
final class ValidateCapacitySchema
{
    private SchemaValidator $validator;
    private string          $schemaDir;

    public function __construct(SchemaValidator|null $validator = null, string|null $schemaDir = null)
    {
        $this->validator = $validator ?? new SchemaValidator();
        $this->schemaDir = $schemaDir ?? __DIR__ . '/../../../schemas';
    }

    /**
     * Validate a capacity.yaml file.
     */
    public function execute(string $capacityPath) : SchemaValidationResult
    {
        $schemaPath = $this->schemaDir . '/capacity-schema.yaml';

        return $this->validator->validateFile(
            schemaPath: $schemaPath,
            targetPath: $capacityPath,
        );
    }

    /**
     * Validate a capacity config array.
     *
     * @param array<int|string, mixed> $config
     */
    public function executeFromArray(array $config) : SchemaValidationResult
    {
        $schemaPath = $this->schemaDir . '/capacity-schema.yaml';
        $schema     = $this->validator->yamlParser()->parseFile($schemaPath);

        return $this->validator->validate(
            schema    : $schema,
            config    : $config,
            schemaName: 'capacity-schema.yaml',
            targetName: 'array',
        );
    }
}
