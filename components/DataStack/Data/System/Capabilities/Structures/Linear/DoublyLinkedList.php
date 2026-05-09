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
 * DoublyLinkedList — an immutable doubly-linked list backed by an array.
 *
 * True immutability in PHP requires that no shared reference can be mutated.
 * A true doubly-linked structure with O(1) prepend+append requires mutable
 * node references, which is incompatible with readonly semantics.
 *
 * This implementation uses array storage to guarantee immutability.
 * prepend and append are O(n) for structural copy, O(1) for first/last access.
 *
 * @implements IteratorAggregate<int, mixed>
 */
final readonly class DoublyLinkedList implements Countable, IteratorAggregate, LinearStructure
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

    public static function empty() : self
    {
        return new self();
    }

    /**
     * @param iterable<mixed> $items
     */
    public static function from(iterable $items) : self
    {
        return new self(items: $items);
    }

    public function prepend(mixed $value) : self
    {
        return new self(items: [$value, ...$this->items]);
    }

    public function append(mixed $value) : self
    {
        return new self(items: [...$this->items, $value]);
    }

    #[Override]
    public function first(mixed $default = null) : mixed
    {
        return $this->items[0] ?? $default;
    }

    #[Override]
    public function last(mixed $default = null) : mixed
    {
        return $this->items === [] ? $default : $this->items[array_key_last(array: $this->items)];
    }

    public function tail() : self
    {
        if ($this->items === []) {
            throw EmptyStructure::forOperation(operation: 'tail');
        }

        return new self(items: array_slice(array: $this->items, offset: 1));
    }

    public function init() : self
    {
        if ($this->items === []) {
            throw EmptyStructure::forOperation(operation: 'init');
        }

        return new self(items: array_slice(array: $this->items, offset: 0, length: -1));
    }

    /**
     * @return list<mixed>
     */
    #[Override]
    public function toArray() : array
    {
        return $this->items;
    }

    /**
     * @return list<mixed>
     */
    public function reverseToArray() : array
    {
        return array_reverse(array: $this->items);
    }

    public function reverse() : self
    {
        return new self(items: array_reverse(array: $this->items));
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
