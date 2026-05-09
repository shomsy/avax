<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\LinearStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\LinkedNodeStorage;
use Avax\Components\DataStack\Data\System\Foundation\Failure\EmptyStructure;
use Countable;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * LinkedList — an immutable singly-linked list.
 *
 * prepend is O(1). traversal is O(n).
 *
 * @implements IteratorAggregate<int, mixed>
 */
final readonly class LinkedList implements Countable, IteratorAggregate, LinearStructure
{
    /**
     * @param LinkedNodeStorage<mixed>|null $head
     */
    private function __construct(private mixed $head = null) {}

    public static function empty() : self
    {
        return new self();
    }

    /**
     * @param iterable<mixed> $items
     */
    public static function from(iterable $items) : self
    {
        $list = new self();
        foreach ($items as $item) {
            $list = $list->prepend(value: $item);
        }

        return $list->reverse();
    }

    public function prepend(mixed $value) : self
    {
        $node = new LinkedNodeStorage(value: $value, next: $this->head);

        return new self(head: $node);
    }

    public function append(mixed $value) : self
    {
        if ($this->head === null) {
            return new self(head: new LinkedNodeStorage(value: $value));
        }

        return new self(head: $this->appendNode(node: $this->head, value: $value));
    }

    /**
     * @param LinkedNodeStorage<mixed> $node
     * @return LinkedNodeStorage<mixed>
     */
    private function appendNode(LinkedNodeStorage $node, mixed $value) : LinkedNodeStorage
    {
        if ($node->next === null) {
            return new LinkedNodeStorage(value: $node->value, next: new LinkedNodeStorage(value: $value));
        }

        return new LinkedNodeStorage(value: $node->value, next: $this->appendNode(node: $node->next, value: $value));
    }

    public function head(mixed $default = null) : mixed
    {
        return $this->head !== null ? $this->head->value : $default;
    }

    #[Override]
    public function first(mixed $default = null) : mixed
    {
        return $this->head(default: $default);
    }

    #[Override]
    public function last(mixed $default = null) : mixed
    {
        if ($this->head === null) {
            return $default;
        }

        $current = $this->head;
        while ($current->next !== null) {
            $current = $current->next;
        }

        return $current->value;
    }

    public function tail() : self
    {
        if ($this->head === null) {
            throw EmptyStructure::forOperation(operation: 'tail');
        }

        return new self(head: $this->head->next);
    }

    public function reverse() : self
    {
        $reversed = null;
        $current = $this->head;
        while ($current !== null) {
            $reversed = new LinkedNodeStorage(value: $current->value, next: $reversed);
            $current = $current->next;
        }

        return new self(head: $reversed);
    }

    /**
     * @return list<mixed>
     */
    #[Override]
    public function toArray() : array
    {
        if ($this->head === null) {
            return [];
        }

        return $this->head->toArray();
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->head === null;
    }

    #[Override]
    public function count() : int
    {
        $count = 0;
        $current = $this->head;
        while ($current !== null) {
            $count++;
            $current = $current->next;
        }

        return max(0, $count);
    }

    #[Override]
    public function getIterator() : Traversable
    {
        return new ArrayIterator(array: $this->toArray());
    }
}
