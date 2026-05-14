<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\ValidateScenariosSchema;

use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidationResult;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidator;

/**
 * Validates a scenarios.yaml file against the scenarios schema.
 *
 * @experimental V3 labs
 */
final readonly class ValidateScenariosSchema
{
    public function __construct(
        private SchemaValidator $validator,
        private string          $schemaDir,
    ) {}

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
