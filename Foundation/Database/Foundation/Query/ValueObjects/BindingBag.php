<?php

declare(strict_types=1);

namespace Avax\Database\Query\ValueObjects;

/**
 * Immutable container for query parameter bindings.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryStates.md
 */
final readonly class BindingBag
{
    /**
     * @param  array<array-key, mixed>  $values  The internal storage for raw, sanitized query parameters.
     */
    public function __construct(
        private array $values = []
    ) {}

    /**
     * Create a new bag with an additional parameter value.
     */
    public function with(mixed $value): self
    {
        $values = $this->values;
        $values[] = $value;

        return new self(values: $values);
    }

    /**
     * Merge multiple parameters into a new bag instance.
     */
    public function merge(array $parameters): self
    {
        return new self(values: array_merge($this->values, $parameters));
    }

    /**
     * Retrieve all bound parameters as an ordered array.
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * Check if the bag contains no bindings.
     */
    public function isEmpty(): bool
    {
        return empty($this->values);
    }
}
