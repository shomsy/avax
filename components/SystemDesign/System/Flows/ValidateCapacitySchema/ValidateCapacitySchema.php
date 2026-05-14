<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\ValidateCapacitySchema;

use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidationResult;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidator;

/**
 * Validates a capacity.yaml file against the capacity schema.
 *
 * @experimental V3 labs
 */
final readonly class ValidateCapacitySchema
{
    public function __construct(
        private SchemaValidator $validator,
        private string          $schemaDir,
    ) {}

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
