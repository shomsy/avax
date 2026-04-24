<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Internal\Mutability;

/**
 * Shared immutable state bag for array-backed values.
 */
final readonly class ImmutableState
{
    public function __construct(
        public array $items = [],
        public bool  $locked = false,
    ) {}

    public function withItems(array $items) : self
    {
        return new self(items: $items, locked: $this->locked);
    }

    public function withLock(bool $locked) : self
    {
        return new self(items: $this->items, locked: $locked);
    }

    public function isEmpty() : bool
    {
        return $this->items === [];
    }

    public function isNotEmpty() : bool
    {
        return $this->items !== [];
    }

    public function count() : int
    {
        return count(value: $this->items);
    }
}
