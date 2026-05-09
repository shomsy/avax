<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Sets;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\Operators\Ordering\Comparator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Bag — a collection that preserves duplicate count.
 *
 * Unlike Set, the same value may appear multiple times.
 * Each occurrence is tracked with a positive count.
 *
 * @implements IteratorAggregate<int, mixed>
 */
final readonly class Bag implements Countable, IteratorAggregate
{
    /**
     * @param array<string, array{value: mixed, count: int<1, max>}> $items
     */
    private function __construct(
        private array $items,
        private int $total,
    ) {}

    /**
     * @param iterable<mixed> $items
     */
    public static function from(iterable $items = []) : self
    {
        $entries = [];
        $total = 0;

        foreach ($items as $item) {
            $hash = self::hashOf(value: $item);
            if (isset($entries[$hash])) {
                $entries[$hash] = ['value' => $item, 'count' => $entries[$hash]['count'] + 1];
            } else {
                $entries[$hash] = ['value' => $item, 'count' => 1];
            }
            $total++;
        }

        return new self(items: $entries, total: $total);
    }

    private static function hashOf(mixed $value) : string
    {
        try {
            return 'j:' . Comparator::hash(value: $value);
        } catch (\JsonException) {
            return 's:' . serialize(value: $value);
        }
    }

    public function add(mixed $value) : self
    {
        return self::from(items: [...$this->flatten(), $value]);
    }

    /**
     * @return list<mixed>
     */
    private function flatten() : array
    {
        $result = [];
        foreach ($this->items as $entry) {
            for ($i = 0; $i < $entry['count']; $i++) {
                $result[] = $entry['value'];
            }
        }

        return $result;
    }

    public function remove(mixed $value) : self
    {
        $hash = self::hashOf(value: $value);
        if (! isset($this->items[$hash])) {
            return $this;
        }

        $items = $this->items;
        if ($items[$hash]['count'] === 1) {
            unset($items[$hash]);
        } else {
            $items[$hash] = ['value' => $items[$hash]['value'], 'count' => $items[$hash]['count'] - 1];
        }

        return new self(items: $items, total: $this->total - 1);
    }

    public function countOf(mixed $value) : int
    {
        $hash = self::hashOf(value: $value);

        return $this->items[$hash]['count'] ?? 0;
    }

    public function contains(mixed $value) : bool
    {
        return $this->countOf(value: $value) > 0;
    }

    /**
     * @param iterable<mixed> $items
     */
    public function union(iterable $items) : self
    {
        return self::from(items: [...$this->flatten(), ...$items]);
    }

    /**
     * @return list<mixed>
     */
    public function toArray() : array
    {
        return $this->flatten();
    }

    /**
     * @return array<string, int<0, max>>
     */
    public function frequencies() : array
    {
        $freq = [];
        foreach ($this->items as $hash => $entry) {
            $freq[$hash] = $entry['count'];
        }

        return $freq;
    }

    public function isEmpty() : bool
    {
        return $this->items === [];
    }

    public function count() : int
    {
        return max(0, $this->total);
    }

    #[\Override]
    public function getIterator() : Traversable
    {
        return new ArrayIterator(array: $this->flatten());
    }
}
