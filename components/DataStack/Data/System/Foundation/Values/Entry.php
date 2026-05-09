<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

/**
 * Entry — a named key-value pair.
 *
 * Used for map entries, configuration entries, and named data associations.
 */
final readonly class Entry
{
    public function __construct(
        public int|string $key,
        public mixed      $value,
    ) {}

    public function withValue(mixed $value) : self
    {
        return new self(key: $this->key, value: $value);
    }

    /**
     * @return array{0: int|string, 1: mixed}
     */
    public function toArray() : array
    {
        return [$this->key, $this->value];
    }
}
