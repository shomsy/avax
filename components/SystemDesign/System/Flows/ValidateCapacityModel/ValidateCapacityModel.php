<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\ValidateCapacityModel;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\NativeYamlParser;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidationResult;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidator;

/**
 * Validates a capacity.yaml file: schema validation + model validation.
 *
 * @experimental V3 labs
 */
final class ValidateCapacityModel
{
    private SchemaValidator $schemaValidator;
    private string $schemaDir;
    private NativeYamlParser $yamlParser;

    public function __construct(
        ?SchemaValidator $schemaValidator = null,
        ?string $schemaDir = null,
        ?NativeYamlParser $yamlParser = null,
    ) {
        $this->schemaValidator = $schemaValidator ?? new SchemaValidator();
        $this->schemaDir = $schemaDir ?? __DIR__ . '/../../../schemas';
        $this->yamlParser = $yamlParser ?? new NativeYamlParser();
    }

    /**
     * Full validation: schema + model.
     *
     * @return array{valid: bool, schema_errors: list<string>, model_errors?: list<string>, model: ?CapacityModel}
     */
    public function execute(string $capacityPath) : array
    {
        // Step 1: Schema validation
        $schemaResult = $this->schemaValidator->validateFile(
            $this->schemaDir . '/capacity-schema.yaml',
            $capacityPath,
        );

        if (! $schemaResult->valid) {
            return [
                'valid' => false,
                'schema_errors' => $schemaResult->errors,
                'model' => null,
            ];
        }

        // Step 2: Model validation
        $config = $this->yamlParser->parseFile($capacityPath);
        $model = CapacityModel::fromConfig($config);
        $modelResult = $model->validate();

        return [
            'valid' => $modelResult['valid'],
            'schema_errors' => [],
            'model_errors' => $modelResult['errors'],
            'model' => $modelResult['valid'] ? $model : null,
        ];
    }

    /**
     * Schema-only validation.
     */
    public function validateSchema(string $capacityPath) : SchemaValidationResult
    {
        return $this->schemaValidator->validateFile(
            $this->schemaDir . '/capacity-schema.yaml',
            $capacityPath,
        );
    }

    /**
     * Parse and build model without full validation.
     *
     * @param array<int|string, mixed> $config
     */
    public function buildModel(array $config) : CapacityModel
    {
        return CapacityModel::fromConfig($config);
    }
}
