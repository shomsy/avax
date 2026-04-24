<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Structures\RingBuffer;

use ArrayIterator;
use Avax\DataFoundation\Exceptions\InvalidStructureException;
use Avax\DataFoundation\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Fixed-capacity circular buffer.
 */
final readonly class RingBuffer implements IteratorAggregate, Countable
{
    /**
     * @var array<int, mixed>
     */
    private array $items;

    /**
     * @param iterable<mixed> $items
     */
    public function __construct(
        private int $capacity,
        iterable    $items = [],
    )
    {
        if ($capacity <= 0) {
            throw InvalidStructureException::capacityMustBePositive(capacity: $capacity);
        }

        $normalized = array_values(NormalizedIterable::toArrayPreserveKeys(iterable: $items));

        if (count($normalized) > $capacity) {
            $normalized = array_slice($normalized, -$capacity);
        }

        $this->items = $normalized;
    }

    public function append(mixed $value) : self
    {
        $items = $this->items;

        if (count($items) === $this->capacity) {
            array_shift($items);
        }

        $items[] = $value;

        return new self(capacity: $this->capacity, items: $items);
    }

    /**
     * @return array<int, mixed>
     */
    public function all() : array
    {
        return $this->items;
    }

    public function capacity() : int
    {
        return $this->capacity;
    }

    public function count() : int
    {
        return count($this->items);
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->items);
    }
}
