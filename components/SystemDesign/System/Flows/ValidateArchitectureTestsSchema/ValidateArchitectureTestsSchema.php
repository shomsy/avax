<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\ValidateArchitectureTestsSchema;

use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidationResult;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidator;

/**
 * Validates an architecture-tests.yaml file against the architecture-tests schema.
 *
 * @experimental V3 labs
 */
final readonly class ValidateArchitectureTestsSchema
{
    public function __construct(
        private SchemaValidator $validator,
        private string          $schemaDir,
    ) {}

    /**
     * Validate an architecture-tests.yaml file.
     */
    public function execute(string $testsPath) : SchemaValidationResult
    {
        $schemaPath = $this->schemaDir . '/architecture-tests-schema.yaml';

        return $this->validator->validateFile(
            schemaPath: $schemaPath,
            targetPath: $testsPath,
        );
    }

    /**
     * Validate an architecture-tests config array.
     *
     * @param array<int|string, mixed> $config
     */
    public function executeFromArray(array $config) : SchemaValidationResult
    {
        $schemaPath = $this->schemaDir . '/architecture-tests-schema.yaml';
        $schema     = $this->validator->yamlParser()->parseFile($schemaPath);

        return $this->validator->validate(
            schema    : $schema,
            config    : $config,
            schemaName: 'architecture-tests-schema.yaml',
            targetName: 'array',
        );
    }
}
