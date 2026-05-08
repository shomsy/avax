<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\OrderedSet;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\DataList\DataList;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\Comparator;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\NormalizedIterable;
use Countable;
use IteratorAggregate;
use JsonException;
use Override;
use Traversable;

/**
 * Unique value collection that preserves insertion order.
 */
final readonly class OrderedSet implements Countable, IteratorAggregate
{
    /**
     * @var array<int, mixed>
     */
    private array $items;

    /**
     * @param  iterable<mixed>  $items
     */
    public function __construct(
        iterable $items = [],
    ) {
        $seen = [];
        $ordered = [];

        foreach (NormalizedIterable::toArrayPreserveKeys(iterable: $items) as $item) {
            try {
                $hash = Comparator::hash(value: $item);
            } catch (JsonException) {
                $hash = serialize($item);
            }

            if (isset($seen[$hash])) {
                continue;
            }

            $seen[$hash] = true;
            $ordered[] = $item;
        }

        $this->items = $ordered;
    }

    /**
     * @return array<int, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function add(mixed $value): self
    {
        return new self(items: [...$this->items, $value]);
    }

    public function contains(mixed $value): bool
    {
        try {
            $hash = Comparator::hash(value: $value);
        } catch (JsonException) {
            $hash = serialize($value);
        }

        foreach ($this->items as $item) {
            try {
                $itemHash = Comparator::hash(value: $item);
            } catch (JsonException) {
                $itemHash = serialize($item);
            }

            if ($itemHash === $hash) {
                return true;
            }
        }

        return false;
    }

    public function toDataList(): DataList
    {
        return new DataList(items: $this->items);
    }

    #[Override]
    public function count(): int
    {
        return count($this->items);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator(array: $this->items);
    }
}
