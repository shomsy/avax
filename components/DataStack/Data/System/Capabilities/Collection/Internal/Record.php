<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal;

/**
 * Immutable named field record.
 */
final readonly class Record
{
    /**
     * @param  array<string, mixed>  $fields
     */
    private function __construct(
        private array $fields,
    ) {
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public static function fromArray(array $fields): self
    {
        return new self(fields: $fields);
    }

    public static function fromFields(RecordField ...$recordField): self
    {
        $mapped = [];

        foreach ($recordField as $field) {
            $mapped[$field->name()] = $field->value();
        }

        return new self(fields: $mapped);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->fields);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->fields[$name] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->fields;
    }
}
