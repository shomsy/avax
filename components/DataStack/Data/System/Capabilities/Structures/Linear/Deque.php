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
final readonly class Deque implements Countable, IteratorAggregate, LinearStructure
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

    public function pushFront(mixed $value) : self
    {
        return new self(items: [$value, ...$this->items]);
    }

    public function pushBack(mixed $value) : self
    {
        return new self(items: [...$this->items, $value]);
    }

    public function popFront() : self
    {
        if ($this->items === []) {
            throw EmptyStructure::forOperation(operation: 'pop front');
        }

        return new self(items: array_slice(array: $this->items, offset: 1));
    }

    public function popBack() : self
    {
        if ($this->items === []) {
            throw EmptyStructure::forOperation(operation: 'pop back');
        }

        $items = $this->items;
        array_pop(array: $items);

        return new self(items: $items);
    }

    #[Override]
    public function first(mixed $default = null) : mixed
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
