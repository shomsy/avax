<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\LinearStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\EmptyStructure;
use Countable;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * @implements IteratorAggregate<int, mixed>
 */
final readonly class Queue implements Countable, IteratorAggregate, LinearStructure
{
    /** @var list<mixed> */
    private array $items;

    /**
     * @param iterable<mixed> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->items = array_values(is_array(value: $items) ? $items : iterator_to_array(iterator: $items));
    }

    public function enqueue(mixed $value) : self
    {
        return new self(items: [...$this->items, $value]);
    }

    public function dequeue() : self
    {
        if ($this->items === []) {
            throw EmptyStructure::forOperation(operation: 'dequeue');
        }

        return new self(items: array_slice(array: $this->items, offset: 1));
    }

    #[Override]
    public function first(mixed $default = null) : mixed
    {
        return $this->front(default: $default);
    }

    public function front(mixed $default = null) : mixed
    {
        return $this->items[0] ?? $default;
    }

    #[Override]
    public function last(mixed $default = null) : mixed
    {
        if ($this->items === []) {
            return $default;
        }

        return $this->items[array_key_last(array: $this->items)];
    }

    /**
     * @return list<mixed>
     */
    #[Override]
    public function toArray() : array
    {
        return $this->items;
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->items === [];
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->items);
    }

    #[Override]
    public function getIterator() : Traversable
    {
        return new ArrayIterator(array: $this->items);
    }
}
