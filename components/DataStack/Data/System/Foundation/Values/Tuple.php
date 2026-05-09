<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

use OutOfBoundsException;

/**
 * Tuple — a fixed-size ordered collection of heterogeneous values.
 */
final readonly class Tuple
{
    /** @var list<mixed> */
    private array $values;

    public function __construct(mixed ...$values)
    {
        $this->values = array_values(array: $values);
    }

    public function size() : int
    {
        return count(value: $this->values);
    }

    public function at(int $index) : mixed
    {
        if ($index < 0 || $index >= count(value: $this->values)) {
            throw new OutOfBoundsException(message: "Index {$index} out of bounds for tuple of size " . count(value: $this->values));
        }

        return $this->values[$index];
    }

    /**
     * @return list<mixed>
     */
    public function toArray() : array
    {
        return $this->values;
    }

    public function first(mixed $default = null) : mixed
    {
        return $this->values[0] ?? $default;
    }

    public function last(mixed $default = null) : mixed
    {
        return $this->values[count(value: $this->values) - 1] ?? $default;
    }
}
