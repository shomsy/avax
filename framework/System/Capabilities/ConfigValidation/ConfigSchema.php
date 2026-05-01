<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ConfigValidation;

final readonly class ConfigSchema
{
    private array $fields = [];

    public function field(string $name, string $type = 'mixed') : ConfigSchemaField
    {
        $field               = new ConfigSchemaField($name, $type);
        $this->fields[$name] = $field;

        return $field;
    }

    public function validate(array $data) : ConfigValidationResult
    {
        return new ConfigValidationResult(valid: true, errors: []);
    }
}

final readonly class ConfigSchemaField
{
    public function __construct(
        public string $name,
        public string $type,
    ) {}
}

final readonly class ConfigValidationResult
{
    public function __construct(
        public bool  $valid,
        public array $errors = [],
    ) {}
}