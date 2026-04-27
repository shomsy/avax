<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Composites;

/**
 * Type-safe immutable record with named fields.
 */
final readonly class Record
{
    /** @var array<string, RecordField> */
    private array $fields;

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    public function get(string $name) : mixed
    {
        return $this->fields[$name]?->value();
    }

    public function toArray() : array
    {
        return array_map(fn (RecordField $f) => $f->value(), $this->fields);
    }
}
