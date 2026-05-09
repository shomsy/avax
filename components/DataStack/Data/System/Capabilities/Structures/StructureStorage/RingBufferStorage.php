<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

use Avax\Components\DataStack\Data\System\Foundation\Failure\EmptyStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use Countable;
use Override;

final readonly class RingBufferStorage implements Countable, StructureStorage
{
    /** @var list<mixed> */
    private array $items;

    /**
     * @param iterable<mixed> $items
     */
    public function __construct(
        private int $capacity,
        iterable    $items = [],
    )
    {
        if ($capacity < 1) {
            throw InvalidCapacity::because(reason: 'Ring buffer capacity must be greater than zero.');
        }

        $normalized = array_values(is_array(value: $items) ? $items : iterator_to_array(iterator: $items));

        if (count(value: $normalized) > $capacity) {
            throw InvalidCapacity::because(reason: 'Ring buffer items cannot exceed capacity.');
        }

        $this->items = $normalized;
    }

    public function capacity() : int
    {
        return $this->capacity;
    }

    /**
     * @return list<mixed>
     */
    public function values() : array
    {
        return $this->items;
    }

    public function enqueue(mixed $value) : self
    {
        if ($this->isFull()) {
            throw InvalidCapacity::because(reason: 'Ring buffer is full.');
        }

        return new self(capacity: $this->capacity, items: [...$this->items, $value]);
    }

    public function isFull() : bool
    {
        return count(value: $this->items) === $this->capacity;
    }

    public function front() : mixed
    {
        if ($this->items === []) {
            throw EmptyStructure::forOperation(operation: 'read front value');
        }

        return $this->items[0];
    }

    public function dequeue() : self
    {
        if ($this->items === []) {
            throw EmptyStructure::forOperation(operation: 'dequeue');
        }

        return new self(capacity: $this->capacity, items: array_slice(array: $this->items, offset: 1));
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->items);
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->items === [];
    }
}
