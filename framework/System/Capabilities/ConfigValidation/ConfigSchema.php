<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ConfigValidation;

/**
 * Defines the expected shape of a configuration section.
 */
final readonly class ConfigSchema
{
    /**
     * @param  array<string, ConfigSchemaField>  $fields
     */
    public function __construct(
        public string $name,
        public array $fields = [],
        public ?string $description = null,
    ) {
    }

    public static function make(string $name): self
    {
        return new self(name: $name);
    }

    /**
     * @param  list<mixed>  $allowed
     */
    public function field(
        string $name,
        string $type = ConfigSchemaField::TYPE_STRING,
        bool $required = true,
        mixed $default = null,
        array $allowed = [],
        ?string $description = null,
    ): self {
        $fields = $this->fields;
        $fields[$name] = new ConfigSchemaField(
            name       : $name,
            type       : $type,
            required   : $required,
            default    : $default,
            allowed    : $allowed,
            description: $description,
        );

        return new self(
            name       : $this->name,
            fields     : $fields,
            description: $this->description,
        );
    }
}
