<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Flows\ValidateScenariosSchema;

use Avax\Labs\SystemDesignKit\System\Capabilities\SchemaValidation\SchemaValidationResult;
use Avax\Labs\SystemDesignKit\System\Capabilities\SchemaValidation\SchemaValidator;

/**
 * Validates a scenarios.yaml file against the scenarios schema.
 *
 * @experimental V3 labs
 */
final class ValidateScenariosSchema
{
    private SchemaValidator $validator;
    private string          $schemaDir;

    public function __construct(?SchemaValidator $validator = null, ?string $schemaDir = null)
    {
        $this->validator = $validator ?? new SchemaValidator();
        $this->schemaDir = $schemaDir ?? __DIR__ . '/../../../schemas';
    }

    /**
     * Validate a scenarios.yaml file.
     */
    public function execute(string $scenariosPath) : SchemaValidationResult
    {
        $schemaPath = $this->schemaDir . '/scenarios-schema.yaml';

        return $this->validator->validateFile(
            schemaPath: $schemaPath,
            targetPath: $scenariosPath,
        );
    }

    /**
     * Validate a scenarios config array.
     *
     * @param array<int|string, mixed> $config
     */
    public function executeFromArray(array $config) : SchemaValidationResult
    {
        $schemaPath = $this->schemaDir . '/scenarios-schema.yaml';
        $schema     = $this->validator->yamlParser()->parseFile($schemaPath);

        return $this->validator->validate(
            schema    : $schema,
            config    : $config,
            schemaName: 'scenarios-schema.yaml',
            targetName: 'array',
        );
    }
}
